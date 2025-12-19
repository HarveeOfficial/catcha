<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Catch Analytics Report</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            line-height: 1.2;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 8px;
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 5px;
        }

        .header h1 {
            font-size: 16px;
            color: #1f2937;
            margin: 0 0 2px 0;
        }

        .header p {
            color: #6b7280;
            font-size: 8px;
            margin: 0;
        }

        .meta-info {
            margin-bottom: 8px;
            background-color: #f3f4f6;
            padding: 5px;
            font-size: 8px;
        }

        .meta-info span {
            margin-right: 15px;
        }

        .meta-label {
            font-weight: bold;
            color: #374151;
        }

        .meta-value {
            color: #6b7280;
        }

        .section {
            margin-bottom: 10px;
        }

        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 5px;
            padding-bottom: 3px;
            border-bottom: 1px solid #d1d5db;
        }

        .summary-cards {
            width: 100%;
            margin-bottom: 10px;
        }

        .summary-cards table {
            width: 100%;
        }

        .summary-cards td {
            width: 25%;
            padding: 5px;
            border: 1px solid #e5e7eb;
            background-color: #f9fafb;
            vertical-align: top;
        }

        .card-title {
            font-size: 7px;
            font-weight: bold;
            color: #6b7280;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .card-value {
            font-size: 12px;
            font-weight: bold;
            color: #1f2937;
        }

        .card-subtitle {
            font-size: 7px;
            color: #9ca3af;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
            font-size: 8px;
        }

        table thead {
            background-color: #3b82f6;
            color: white;
        }

        table th {
            padding: 3px 5px;
            text-align: left;
            font-weight: 600;
            border-bottom: 1px solid #3b82f6;
        }

        table td {
            padding: 3px 5px;
            border-bottom: 0.5px solid #e5e7eb;
        }

        table tbody tr:nth-child(odd) {
            background-color: #f9fafb;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: 600;
        }

        .text-sm {
            font-size: 7px;
        }

        .text-xs {
            font-size: 6px;
        }

        .footer {
            margin-top: 6px;
            padding-top: 4px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 6px;
            color: #9ca3af;
            line-height: 1.1;
        }

        .empty-state {
            background-color: #f3f4f6;
            padding: 3px;
            text-align: center;
            color: #6b7280;
            font-style: italic;
            font-size: 7px;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
        <!-- Header -->
        <div class="header">
            <h1>Catch Analytics Report</h1>
            <p>Comprehensive fishing catch statistics and insights</p>
        </div>

        <!-- Meta Information -->
        <div class="meta-info">
            <span class="meta-label">Period:</span>
            <span class="meta-value">
                @if($dateFrom && $dateTo)
                    {{ $dateFrom->format('M d, Y') }} - {{ $dateTo->format('M d, Y') }}
                @else
                    All Time
                @endif
            </span>
            <span class="meta-label">Generated:</span>
            <span class="meta-value">{{ $generatedAt->format('M d, Y \a\t H:i') }}</span>
        </div>

        <!-- Summary Cards -->
        <div class="section">
            <div class="section-title">Summary</div>
            <div class="summary-cards">
                <table>
                    <tr>
                        <td>
                            <div class="card-title">Total Catches</div>
                            <div class="card-value">{{ $totalSummary->catches ?? 0 }}</div>
                            <div class="card-subtitle">recorded</div>
                        </td>
                        <td>
                            <div class="card-title">Total Quantity</div>
                            <div class="card-value">{{ number_format($totalSummary->total_qty ?? 0, 2) }}</div>
                            <div class="card-subtitle">kg</div>
                        </td>
                        <td>
                            <div class="card-title">Fish Count</div>
                            <div class="card-value">{{ $totalSummary->total_count ?? 0 }}</div>
                            <div class="card-subtitle">pcs</div>
                        </td>
                        <td>
                            <div class="card-title">Average Size</div>
                            <div class="card-value">{{ $totalSummary->avg_size ? number_format($totalSummary->avg_size, 1) : '—' }}</div>
                            <div class="card-subtitle">cm per fish</div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Top Species -->
        <div class="section">
            <div class="section-title">Top Species (by quantity)</div>
            @if($topSpecies && $topSpecies->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Species</th>
                            <th class="text-right">Qty (kg)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topSpecies as $species)
                            <tr>
                                <td>{{ $species->species?->common_name ?? 'Unknown' }}</td>
                                <td class="text-right font-bold">{{ number_format($species->qty_sum, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-state">No species data available</div>
            @endif
        </div>

        <!-- Gear Breakdown -->
        <div class="section">
            <div class="section-title">Gear Breakdown</div>
            @if($gearBreakdown && $gearBreakdown->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Gear</th>
                            <th class="text-right">Qty (kg)</th>
                            <th class="text-right">Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($gearBreakdown as $gear)
                            <tr>
                                <td>{{ $gear->gear_type }}</td>
                                <td class="text-right">{{ number_format($gear->qty, 2) }}</td>
                                <td class="text-right">{{ $gear->catches }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-state">No gear data available</div>
            @endif
        </div>

        <!-- Zone Breakdown -->
        <div class="section">
            <div class="section-title">Zone Breakdown</div>
            @if($zoneBreakdown && $zoneBreakdown->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Zone</th>
                            <th class="text-right">Qty (kg)</th>
                            <th class="text-right">Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($zoneBreakdown as $zone)
                            <tr>
                                <td>{{ $zone->zone?->name ?? 'Unknown' }}</td>
                                <td class="text-right">{{ number_format($zone->qty, 2) }}</td>
                                <td class="text-right">{{ $zone->catches }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-state">No zone data available</div>
            @endif
        </div>

        <!-- Monthly Series -->
        <div class="section">
            <div class="section-title">Monthly Summary (Last 6 months)</div>
            @if($monthlySeries && $monthlySeries->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th class="text-right">Qty (kg)</th>
                            <th class="text-right">Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($monthlySeries as $month)
                            <tr>
                                <td>{{ $month->ym }}</td>
                                <td class="text-right">{{ number_format($month->qty, 2) }}</td>
                                <td class="text-right">{{ $month->catch_count }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-state">No monthly data available</div>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>This report was automatically generated on {{ $generatedAt->format('M d, Y \a\t H:i:s') }}</p>
            <p>Catcha - Fishing Analytics System</p>
        </div>
</body>
</html>
