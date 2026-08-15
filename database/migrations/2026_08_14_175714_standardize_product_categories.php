<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Convert old categories to valid ones (Example mappings)
        DB::table('products')->where('Category', 'Bikes')->update(['Category' => 'Whole Bikes']);
        DB::table('products')->where('Category', 'Parts')->update(['Category' => 'Bike Parts']);
        DB::table('products')->where('Category', 'Accs')->update(['Category' => 'Accessories']);

        // 2. Set any null, empty, or unknown categories/subcategories to a default fallback
        DB::table('products')
            ->whereNotIn('Category', ['Whole Bikes', 'Bike Parts', 'Accessories'])
            ->orWhereNull('Category')
            ->update([
                'Category' => 'Bike Parts',
                'SubCategory' => 'Miscellaneous'
            ]);

        // 3. Fix any orphan subcategories that don't match the new list
        DB::table('products')
            ->where('Category', 'Whole Bikes')
            ->whereNotIn('SubCategory', [
                'Mountain Bikes', 'Road Bikes', 'Fixie Bikes', 'Gravel Bikes', 
                'Kids Bikes', 'Folding Bikes', 'BMX Bikes', 'Electric Bikes', 'Fat Bikes'
            ])
            ->update(['SubCategory' => 'Mountain Bikes']);

        DB::table('products')
            ->where('Category', 'Bike Parts')
            ->whereNotIn('SubCategory', [
                'Framesets', 'Cockpit', 'Wheelsets', 'Drivetrain', 'Braking System', 'Miscellaneous'
            ])
            ->update(['SubCategory' => 'Miscellaneous']);

        DB::table('products')
            ->where('Category', 'Accessories')
            ->whereNotIn('SubCategory', [
                'Safety Gear', 'Bike Attachments', 'Security', 'Storage', 'Maintenance', 'Miscellaneous'
            ])
            ->update(['SubCategory' => 'Miscellaneous']);
    }

    public function down(): void
    {
        // No rollback required for data normalization
    }
};