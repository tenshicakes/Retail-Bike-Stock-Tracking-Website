<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Transaction Invoice</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
        .header { width: 100%; margin-bottom: 30px; }
        .shop-info { float: left; width: 60%; }
        .logo-container { float: right; width: 40%; text-align: right; }
        .logo-container img { width: 80px; height: auto; }
        .clear { clear: both; }
        h1 { margin: 0; color: #0c112c; font-size: 24px; }
        .details { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f8f9fa; font-weight: bold; }
        .text-right { text-align: right; }
        .totals { float: right; width: 40%; }
        .totals table { border: none; }
        .totals td { border: none; padding: 4px; }
        .signatures { margin-top: 80px; width: 100%; }
        .sig-block { float: left; width: 45%; text-align: center; }
        .sig-line { border-top: 1px solid #333; margin-top: 50px; padding-top: 5px; }
    </style>
</head>
<body>

    <div class="header">
        <div class="shop-info">
            <h1>Alvin's Bike Repair Shop</h1>
            <p>123 Hub Trail Road<br>
            Mountain View, CA 94043<br>
            Phone: (555) 123-4567</p>
        </div>
        <div class="logo-container">
            
            <h3>TRANSACTION INVOICE</h3>
            <p>Date: {{ now()->format('F j, Y') }}</p>
        </div>
        <div class="clear"></div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Log ID</th>
                <th>Date</th>
                <th>Action</th>
                <th>Item</th>
                <th>Qty</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $log)
                <tr>
                    <td>#{{ $log->LogID }}</td>
                    <td>{{ \Carbon\Carbon::parse($log->LogDate)->format('M d, Y') }}</td>
                    <td>{{ $log->ActionType }}</td>
                    <td>{{ $log->product->ProductName }}</td>
                    <td>{{ $log->Quantity }}</td>
                    <td class="text-right">P{{ number_format($log->UnitPrice, 2) }}</td>
                    <td class="text-right">P{{ number_format($log->TotalPrice, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <table>
            <tr>
                <td><strong>Total Items Processed:</strong></td>
                <td class="text-right">{{ $logs->sum('Quantity') }}</td>
            </tr>
            <tr>
                <td><strong>Grand Total:</strong></td>
                <td class="text-right">
                    <strong>P{{ number_format($logs->sum('TotalPrice'), 2) }}</strong>
                </td>
            </tr>
        </table>
    </div>
    <div class="clear"></div>

    <div class="signatures">
        <div class="sig-block">
            <div class="sig-line">Authorized Shop Signature</div>
        </div>
        <div class="sig-block" style="float: right;">
            <div class="sig-line">Customer / Supplier Signature</div>
        </div>
        <div class="clear"></div>
    </div>

</body>
</html>