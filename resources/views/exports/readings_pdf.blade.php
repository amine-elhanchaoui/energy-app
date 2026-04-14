<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Energy Readings Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #0ea5e9;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            color: #2c3e50;
        }
        .header p {
            margin: 5px 0;
            color: #7f8c8d;
            font-size: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        thead {
            background-color: #0f766e;
            color: white;
        }
        th {
            padding: 12px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #ddd;
        }
        td {
            padding: 10px 12px;
            border: 1px solid #ddd;
        }
        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tbody tr:hover {
            background-color: #f0f0f0;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #7f8c8d;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        .summary {
            margin-top: 20px;
            padding: 15px;
            background-color: #ecfeff;
            border: 1px solid #bae6fd;
            border-radius: 4px;
        }
        .summary h3 {
            margin-top: 0;
            color: #2c3e50;
        }
        .summary p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Energy Consumption Report</h1>
        <p>Generated on {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Meter ID</th>
                <th>User Name</th>
                <th>Meter Type</th>
                <th>Consumption Value (Unit)</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($readings as $reading)
                <tr>
                    <td>{{ $reading->id }}</td>
                    <td>{{ $reading->meter_id }}</td>
                    <td>{{ $reading->meter?->user?->name ?? 'N/A' }}</td>
                    <td>
                        <span style="text-transform: capitalize;">{{ $reading->meter?->type ?? 'N/A' }}</span>
                    </td>
                    <td>
                        {{ number_format($reading->value, 2) }} {{ $reading->meter?->unit ?? '' }}
                    </td>
                    <td>{{ \Carbon\Carbon::parse($reading->date)->format('d/m/Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #7f8c8d;">
                        No readings found
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($readings->count() > 0)
        <div class="summary">
            <h3>Summary Statistics (Last 100 Readings)</h3>
            <p><strong>Total Readings:</strong> {{ $readings->count() }}</p>
            <p><strong>Total Consumption:</strong> {{ number_format($readings->sum('value'), 2) }} @if($readings->first()?->meter?->unit){{ $readings->first()?->meter?->unit }}@endif</p>
            <p><strong>Average Consumption:</strong> {{ number_format($readings->avg('value'), 2) }} @if($readings->first()?->meter?->unit){{ $readings->first()?->meter?->unit }}@endif</p>
            <p><strong>Maximum Reading:</strong> {{ number_format($readings->max('value'), 2) }} @if($readings->first()?->meter?->unit){{ $readings->first()?->meter?->unit }}@endif</p>
            <p><strong>Minimum Reading:</strong> {{ number_format($readings->min('value'), 2) }} @if($readings->first()?->meter?->unit){{ $readings->first()?->meter?->unit }}@endif</p>
        </div>
    @endif

    <div class="footer">
        <p>&copy; Energy Management System - All Rights Reserved</p>
    </div>
</body>
</html>
