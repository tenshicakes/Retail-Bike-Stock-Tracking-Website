<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Log;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class BackupController extends Controller
{
    private function backupDirectory(): string
    {
        $path = storage_path('app/backups');

        if (! File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }

        return $path;
    }

    private function getDatabaseTables(): array
    {
        $rawTables = DB::select('SHOW TABLES');
        $tables = array_map(fn ($row) => (string) array_values((array) $row)[0], $rawTables);

        // Prioritize core tables first so parent tables precede foreign-key child tables
        $priority = ['accounts', 'products', 'logs'];
        usort($tables, function (string $a, string $b) use ($priority): int {
            $posA = array_search($a, $priority, true);
            $posB = array_search($b, $priority, true);

            if ($posA !== false && $posB !== false) {
                return $posA <=> $posB;
            }
            if ($posA !== false) {
                return -1;
            }
            if ($posB !== false) {
                return 1;
            }

            return strcmp($a, $b);
        });

        return $tables;
    }

    public function index()
    {
        $databaseName = DB::getDatabaseName();
        $allTables = $this->getDatabaseTables();

        // Row counts per table
        $tableStats = [];
        foreach ($allTables as $table) {
            $tableStats[$table] = DB::table($table)->count();
        }

        // Core summary metrics
        $accountsCount = Account::count();
        $productsCount = Product::count();
        $logsCount = Log::count();

        // Scan saved .sql backup files in storage/app/backups
        $backupDir = $this->backupDirectory();
        $files = File::files($backupDir);

        $backups = collect($files)
            ->filter(fn ($file) => strtolower($file->getExtension()) === 'sql')
            ->map(function ($file) {
                $name = $file->getFilename();
                $isPartial = str_contains($name, '_partial_');

                return [
                    'filename' => $name,
                    'type' => $isPartial ? 'Selective' : 'Full Backup',
                    'size' => $this->formatBytes($file->getSize()),
                    'created_at' => Carbon::createFromTimestamp($file->getMTime())->format('M d, Y h:i A'),
                    'timestamp' => $file->getMTime(),
                ];
            })
            ->sortByDesc('timestamp')
            ->values()
            ->all();

        $lastBackupTime = ! empty($backups) ? $backups[0]['created_at'] : 'No backups yet';

        return view('dashboard.backup', compact(
            'databaseName',
            'allTables',
            'tableStats',
            'accountsCount',
            'productsCount',
            'logsCount',
            'backups',
            'lastBackupTime'
        ));
    }

    public function create(Request $request)
    {
        $availableTables = $this->getDatabaseTables();
        $mode = $request->input('mode', 'full');

        if ($mode === 'selective') {
            $request->validate([
                'tables' => ['required', 'array', 'min:1'],
                'tables.*' => ['required', 'string', 'in:' . implode(',', $availableTables)],
            ], [
                'tables.required' => 'Please select at least one table to export.',
                'tables.min' => 'Please select at least one table to export.',
            ]);

            // Preserve clean table order
            $selectedTables = array_values(array_intersect($availableTables, $request->tables));
            $prefix = 'partial';
        } else {
            $selectedTables = $availableTables;
            $prefix = 'full';
        }

        $databaseName = DB::getDatabaseName();
        $timestamp = now()->format('Y-m-d_His');
        $filename = "{$databaseName}_{$prefix}_{$timestamp}.sql";

        $sqlDump = $this->generateSqlDump($databaseName, $selectedTables);

        $filePath = $this->backupDirectory() . DIRECTORY_SEPARATOR . $filename;
        File::put($filePath, $sqlDump);

        return response()->json([
            'status' => 'success',
            'message' => "Backup '{$filename}' created successfully!",
            'filename' => $filename,
            'download_url' => route('backup.download', ['filename' => $filename]),
        ]);
    }

    public function download(string $filename)
    {
        $safeFilename = basename($filename);

        if ($safeFilename !== $filename || ! str_ends_with(strtolower($safeFilename), '.sql')) {
            abort(404);
        }

        $filePath = $this->backupDirectory() . DIRECTORY_SEPARATOR . $safeFilename;

        if (! File::exists($filePath)) {
            abort(404, 'Backup file not found.');
        }

        return response()->download($filePath, $safeFilename, [
            'Content-Type' => 'application/sql',
        ]);
    }

    public function destroy(string $filename)
    {
        $safeFilename = basename($filename);

        if ($safeFilename !== $filename || ! str_ends_with(strtolower($safeFilename), '.sql')) {
            abort(404);
        }

        $filePath = $this->backupDirectory() . DIRECTORY_SEPARATOR . $safeFilename;

        if (File::exists($filePath)) {
            File::delete($filePath);
        }

        return response()->json([
            'status' => 'success',
            'message' => "Backup '{$safeFilename}' deleted successfully.",
        ]);
    }

    public function restore(Request $request)
    {
        $request->validate([
            'sql_file' => ['required', 'file', 'max:51200'],
            'admin_password' => ['required', 'string'],
        ], [
            'sql_file.required' => 'Please choose a .sql backup file to restore.',
            'sql_file.max' => 'Backup file must not exceed 50 MB.',
            'admin_password.required' => 'Administrator password is required to confirm database restore.',
        ]);

        $uploadedFile = $request->file('sql_file');
        $extension = strtolower($uploadedFile->getClientOriginalExtension());

        if (! in_array($extension, ['sql', 'txt'], true)) {
            throw ValidationException::withMessages([
                'sql_file' => ['Invalid file format. Only .sql backup files are accepted.'],
            ]);
        }

        $user = Auth::user();
        if (! Hash::check($request->admin_password, $user->Password)) {
            throw ValidationException::withMessages([
                'admin_password' => ['Incorrect Administrator password. Restore aborted.'],
            ]);
        }

        $sqlContent = File::get($uploadedFile->getRealPath());
        $statements = $this->splitSqlStatements($sqlContent);

        if (empty($statements)) {
            throw ValidationException::withMessages([
                'sql_file' => ['The uploaded SQL file contains no executable statements.'],
            ]);
        }

        try {
            DB::unprepared('SET FOREIGN_KEY_CHECKS=0;');

            foreach ($statements as $statement) {
                DB::unprepared($statement . ';');
            }
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Database restore failed: ' . $e->getMessage(),
            ], 500);
        } finally {
            DB::unprepared('SET FOREIGN_KEY_CHECKS=1;');
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Database restored successfully from ' . $uploadedFile->getClientOriginalName() . '!',
        ]);
    }

    private function generateSqlDump(string $databaseName, array $tables): string
    {
        $pdo = DB::connection()->getPdo();
        $userEmail = Auth::user()?->Username ?? 'Administrator';

        $lines = [
            "-- ==========================================================",
            "-- Alvin's Bike Repair Shop - Database Backup",
            "-- Database: `{$databaseName}`",
            "-- Generated At: " . now()->format('Y-m-d H:i:s'),
            "-- Generated By: {$userEmail}",
            "-- Tables Included: " . implode(', ', $tables),
            "-- ==========================================================",
            "",
            'SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";',
            "SET FOREIGN_KEY_CHECKS = 0;",
            "SET time_zone = '+00:00';",
            "",
        ];

        foreach ($tables as $table) {
            $lines[] = "-- ----------------------------------------------------------";
            $lines[] = "-- Table structure for `{$table}`";
            $lines[] = "-- ----------------------------------------------------------";
            $lines[] = "DROP TABLE IF EXISTS `{$table}`;";

            $createResult = (array) DB::selectOne("SHOW CREATE TABLE `{$table}`");
            $createValues = array_values($createResult);
            $createSql = $createValues[1] ?? '';

            $lines[] = $createSql . ";";
            $lines[] = "";

            $rows = DB::table($table)->get();

            if ($rows->isNotEmpty()) {
                $lines[] = "-- Dumping data for table `{$table}` ({$rows->count()} rows)";

                $firstRow = (array) $rows->first();
                $columns = array_map(fn ($col) => "`{$col}`", array_keys($firstRow));
                $columnList = implode(', ', $columns);

                foreach ($rows->chunk(100) as $chunk) {
                    $valueGroups = [];

                    foreach ($chunk as $row) {
                        $rowValues = [];
                        foreach ((array) $row as $value) {
                            if ($value === null) {
                                $rowValues[] = 'NULL';
                            } elseif (is_int($value) || is_float($value)) {
                                $rowValues[] = (string) $value;
                            } else {
                                $rowValues[] = $pdo->quote((string) $value);
                            }
                        }
                        $valueGroups[] = '(' . implode(', ', $rowValues) . ')';
                    }

                    $lines[] = "INSERT INTO `{$table}` ({$columnList}) VALUES\n" . implode(",\n", $valueGroups) . ";";
                }

                $lines[] = "";
            }
        }

        $lines[] = "SET FOREIGN_KEY_CHECKS = 1;";
        $lines[] = "";

        return implode("\n", $lines);
    }

    private function splitSqlStatements(string $sql): array
    {
        // Strip UTF-8 BOM if present
        $sql = preg_replace('/^\xEF\xBB\xBF/', '', $sql) ?? $sql;

        $statements = [];
        $current = '';
        $length = strlen($sql);
        $inSingleQuote = false;
        $inDoubleQuote = false;
        $inBacktick = false;

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];
            $next = $sql[$i + 1] ?? '';

            // Skip single-line comments (-- or #) outside quotes
            if (! $inSingleQuote && ! $inDoubleQuote && ! $inBacktick) {
                if (($char === '-' && $next === '-') || $char === '#') {
                    while ($i < $length && $sql[$i] !== "\n") {
                        $i++;
                    }
                    continue;
                }

                // Skip multi-line comments (/* ... */) outside quotes
                if ($char === '/' && $next === '*') {
                    $i += 2;
                    while ($i < $length && ! ($sql[$i] === '*' && ($sql[$i + 1] ?? '') === '/')) {
                        $i++;
                    }
                    $i++;
                    continue;
                }
            }

            // Handle escaped characters inside quotes
            if (($inSingleQuote || $inDoubleQuote) && $char === '\\') {
                $current .= $char . $next;
                $i++;
                continue;
            }

            if ($char === "'" && ! $inDoubleQuote && ! $inBacktick) {
                $inSingleQuote = ! $inSingleQuote;
            } elseif ($char === '"' && ! $inSingleQuote && ! $inBacktick) {
                $inDoubleQuote = ! $inDoubleQuote;
            } elseif ($char === '`' && ! $inSingleQuote && ! $inDoubleQuote) {
                $inBacktick = ! $inBacktick;
            }

            if ($char === ';' && ! $inSingleQuote && ! $inDoubleQuote && ! $inBacktick) {
                $trimmed = trim($current);
                if ($trimmed !== '') {
                    $statements[] = $trimmed;
                }
                $current = '';
                continue;
            }

            $current .= $char;
        }

        $trimmed = trim($current);
        if ($trimmed !== '') {
            $statements[] = $trimmed;
        }

        return $statements;
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }
        if ($bytes < 1048576) {
            return round($bytes / 1024, 2) . ' KB';
        }

        return round($bytes / 1048576, 2) . ' MB';
    }
}