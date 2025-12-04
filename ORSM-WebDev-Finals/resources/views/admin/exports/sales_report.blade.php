<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sales Report</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 6px; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>
    <h2>Sales Report</h2>
    <div>From: {{ $start->toDateString() }} — To: {{ $end->toDateString() }}</div>
    @if(isset($partIndex) && isset($totalParts))
        <div style="margin-top:6px; font-weight:600;">Part {{ $partIndex }} of {{ $totalParts }}</div>
    @endif
    @if(isset($overallTotalOrders) || isset($overallGrandTotal))
        <div style="margin-top:6px;">Overall Totals:
            @if(isset($overallTotalOrders)) <strong>Orders:</strong> {{ $overallTotalOrders }}; @endif
            @if(isset($overallGrandTotal)) <strong>Sales:</strong> {{ number_format($overallGrandTotal,2) }}; @endif
        </div>
    @endif
    @if(!empty($categoryName) || !empty($supplierName))
        <div style="margin-top:6px;">Filters:
            @if(!empty($categoryName)) <strong>Category:</strong> {{ $categoryName }}; @endif
            @if(!empty($supplierName)) <strong>Supplier:</strong> {{ $supplierName }}; @endif
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Date</th>
                <th>Customer</th>
                <th>Items</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $o)
                <tr>
                    <td>{{ $o->order_id }}</td>
                    <td>{{ optional($o->order_date)->format('Y-m-d H:i') }}</td>
                    <td>{{ $o->customer_id }}</td>
                    <td>{{ $o->details->sum('quantity') }}</td>
                    <td style="text-align:right;">{{ number_format($o->total_amount,2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top:12px; font-weight:bold;">Total Orders: {{ isset($totalOrders) ? $totalOrders : $orders->count() }}</div>
    <div style="font-weight:bold;">Grand Total: {{ isset($grandTotal) ? number_format($grandTotal,2) : number_format($orders->sum('total_amount'),2) }}</div>
</body>
</html>
