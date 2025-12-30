<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Reimbursement - {{ $report->period_label }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
        }

        .page {
            padding: 20px;
            page-break-after: always;
        }

        .page:last-child {
            page-break-after: avoid;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #00bcd4;
        }

        .header h1 {
            font-size: 18px;
            color: #00838f;
            margin-bottom: 5px;
        }

        .header .period {
            font-size: 14px;
            color: #555;
        }

        /* User info */
        .user-info {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f5f5f5;
            border-radius: 5px;
        }

        .user-info table {
            width: 100%;
        }

        .user-info td {
            padding: 3px 5px;
        }

        .user-info .label {
            font-weight: bold;
            width: 120px;
        }

        /* Data Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #ddd;
            padding: 8px 6px;
            text-align: left;
        }

        .data-table th {
            background-color: #00bcd4;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
        }

        .data-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .data-table .amount {
            text-align: right;
            white-space: nowrap;
        }

        .data-table .center {
            text-align: center;
        }

        /* Summary */
        .summary {
            margin-top: 20px;
            padding: 15px;
            background-color: #e0f7fa;
            border-radius: 5px;
        }

        .summary-table {
            width: 100%;
        }

        .summary-table td {
            padding: 5px;
        }

        .summary-table .total-label {
            font-weight: bold;
            font-size: 14px;
        }

        .summary-table .total-amount {
            text-align: right;
            font-weight: bold;
            font-size: 16px;
            color: #00838f;
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 20px;
            left: 20px;
            right: 20px;
            text-align: center;
            font-size: 9px;
            color: #888;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }

        /* Images page */
        .images-grid {
            display: table;
            width: 100%;
        }

        .images-row {
            display: table-row;
        }

        .image-cell {
            display: table-cell;
            width: 50%;
            padding: 10px;
            vertical-align: top;
            text-align: center;
        }

        .image-container {
            border: 1px solid #ddd;
            padding: 10px;
            background: #fff;
            border-radius: 5px;
        }

        .image-container img {
            max-width: 100%;
            max-height: 250px;
            object-fit: contain;
        }

        .image-info {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid #eee;
            font-size: 10px;
            text-align: left;
        }

        .image-info .item-date {
            font-weight: bold;
        }

        .page-title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            color: #00838f;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #00bcd4;
        }
    </style>
</head>
<body>
    <!-- Page 1: Data Table -->
    <div class="page">
        <div class="header">
            <h1>LAPORAN REIMBURSEMENT</h1>
            <div class="period">Periode: {{ $report->period_start->format('d F Y') }} - {{ $report->period_end->format('d F Y') }}</div>
        </div>

        <div class="user-info">
            <table>
                <tr>
                    <td class="label">Nama:</td>
                    <td>{{ $report->user->name }}</td>
                    <td class="label">Tanggal Cetak:</td>
                    <td>{{ now()->format('d F Y H:i') }}</td>
                </tr>
                <tr>
                    <td class="label">Email:</td>
                    <td>{{ $report->user->email }}</td>
                    <td class="label">Status:</td>
                    <td>{{ ucfirst($report->status) }}</td>
                </tr>
            </table>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 30px;">No</th>
                    <th style="width: 70px;">Tanggal</th>
                    <th>Client</th>
                    <th style="width: 80px;">Kategori</th>
                    <th>Keterangan</th>
                    <th style="width: 100px;">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reimbursements as $index => $item)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td class="center">{{ $item->transaction_date->format('d/m/Y') }}</td>
                    <td>{{ $item->client->name }}</td>
                    <td>{{ $item->category->name }}</td>
                    <td>{{ $item->note ?: '-' }}</td>
                    <td class="amount">Rp {{ number_format($item->amount, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary">
            <table class="summary-table">
                <tr>
                    <td class="total-label">Total Entry:</td>
                    <td style="width: 150px;">{{ $report->entry_count }} item</td>
                    <td class="total-label">Total Jumlah:</td>
                    <td class="total-amount">Rp {{ number_format($report->total_amount, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <div class="footer">
            Generated by Reimburse System | {{ config('app.name') }}
        </div>
    </div>

    <!-- Page 2+: Receipt Images (4 per page) -->
    @if($reimbursements->count() > 0)
        @foreach($reimbursements->chunk(4) as $pageIndex => $chunk)
        <div class="page">
            <div class="page-title">
                Bukti Transaksi (Halaman {{ $pageIndex + 1 }} dari {{ ceil($reimbursements->count() / 4) }})
            </div>

            <div class="images-grid">
                @foreach($chunk->chunk(2) as $row)
                <div class="images-row">
                    @foreach($row as $item)
                    <div class="image-cell">
                        <div class="image-container">
                            @if($item->image_path)
                                <img src="{{ Storage::url($item->image_path) }}" alt="Receipt">
                            @else
                                <div style="height: 200px; background: #f5f5f5; display: flex; align-items: center; justify-content: center; color: #999;">
                                    Image not available
                                </div>
                            @endif
                            <div class="image-info">
                                <div class="item-date">{{ $item->transaction_date->format('d/m/Y') }} - {{ $item->client->name }}</div>
                                <div>{{ $item->category->name }}: Rp {{ number_format($item->amount, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    @if($row->count() < 2)
                    <div class="image-cell"></div>
                    @endif
                </div>
                @endforeach
            </div>

            <div class="footer">
                Generated by Reimburse System | {{ config('app.name') }}
            </div>
        </div>
        @endforeach
    @endif
</body>
</html>
