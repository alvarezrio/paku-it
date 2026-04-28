<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Tiket IT Helpdesk - {{ $startDate }} s/d {{ $endDate }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header h1 {
            font-size: 18px;
            margin-bottom: 5px;
        }
        .header p {
            color: #666;
            font-size: 11px;
        }
        .stats-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 25px;
        }
        .stat-box {
            flex: 1;
            min-width: 100px;
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
            border-radius: 4px;
        }
        .stat-box .number {
            font-size: 20px;
            font-weight: bold;
            color: #0066cc;
        }
        .stat-box .label {
            font-size: 10px;
            color: #666;
            text-transform: uppercase;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #ddd;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #fafafa;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        .badge-open { background: #fee2e2; color: #991b1b; }
        .badge-in_progress { background: #fef3c7; color: #92400e; }
        .badge-waiting { background: #dbeafe; color: #1e40af; }
        .badge-resolved { background: #d1fae5; color: #065f46; }
        .badge-closed { background: #f3f4f6; color: #374151; }
        .badge-critical { background: #fee2e2; color: #991b1b; }
        .badge-high { background: #ffedd5; color: #9a3412; }
        .badge-medium { background: #dbeafe; color: #1e40af; }
        .badge-low { background: #f3f4f6; color: #374151; }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .two-col {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .two-col > div {
            flex: 1;
        }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #0066cc; color: white; border: none; border-radius: 4px; cursor: pointer;">
            Cetak / Simpan PDF
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; background: #666; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 10px;">
            Tutup
        </button>
    </div>

    <div class="header">
        <h1>LAPORAN TIKET HELPDESK</h1>
        <p>Periode: {{ $startDate }} s/d {{ $endDate }}</p>
        <p>Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }} WIB</p>
    </div>

    <div class="stats-grid">
        <div class="stat-box">
            <div class="number">{{ $stats['total'] }}</div>
            <div class="label">Total Tiket</div>
        </div>
        <div class="stat-box">
            <div class="number" style="color: #dc2626;">{{ $stats['open'] }}</div>
            <div class="label">Dibuka</div>
        </div>
        <div class="stat-box">
            <div class="number" style="color: #d97706;">{{ $stats['in_progress'] }}</div>
            <div class="label">Diproses</div>
        </div>
        <div class="stat-box">
            <div class="number" style="color: #059669;">{{ $stats['resolved'] + $stats['closed'] }}</div>
            <div class="label">Selesai</div>
        </div>
        <div class="stat-box">
            <div class="number" style="color: #059669;">{{ $stats['resolution_rate'] }}%</div>
            <div class="label">Tingkat Selesai</div>
        </div>
    </div>

    <div class="two-col">
        <div class="section">
            <div class="section-title">Berdasarkan Kategori</div>
            <table>
                <tr>
                    <th>Kategori</th>
                    <th style="text-align: right;">Jumlah</th>
                </tr>
                @foreach($categoryStats as $category => $count)
                <tr>
                    <td>{{ match($category) {
                        'incident_management' => 'Manajemen Insiden',
                        'service_request'     => 'Permintaan Layanan',
                        'user_support'        => 'Dukungan Pengguna',
                        'access_management'   => 'Manajemen Akses',
                        'asset_management'    => 'Manajemen Aset',
                        'change_management'   => 'Manajemen Perubahan',
                        'network_support'     => 'Dukungan Jaringan',
                        'security_support'    => 'Dukungan Keamanan',
                        'documentation_kb'    => 'Dokumentasi & Basis Pengetahuan',
                        default => $category,
                    } }}</td>
                    <td style="text-align: right;">{{ $count }}</td>
                </tr>
                @endforeach
            </table>
        </div>

        <div class="section">
            <div class="section-title">Berdasarkan Prioritas</div>
            <table>
                <tr>
                    <th>Prioritas</th>
                    <th style="text-align: right;">Jumlah</th>
                </tr>
                @foreach($priorityStats as $priority => $count)
                <tr>
                    <td>{{ match($priority) {
                        'critical' => 'Kritis',
                        'high' => 'Tinggi',
                        'medium' => 'Sedang',
                        'low' => 'Rendah',
                        default => $priority,
                    } }}</td>
                    <td style="text-align: right;">{{ $count }}</td>
                </tr>
                @endforeach
            </table>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Daftar Tiket</div>
        <table>
            <thead>
                <tr>
                    <th>No. Tiket</th>
                    <th>Tanggal</th>
                    <th>Pelapor</th>
                    <th>Kategori</th>
                    <th>Prioritas</th>
                    <th>Subjek</th>
                    <th>Status</th>
                    <th>Ditugaskan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tickets as $ticket)
                <tr>
                    <td>{{ $ticket->ticket_number }}</td>
                    <td>{{ $ticket->created_at->format('d/m/Y') }}</td>
                    <td>{{ $ticket->user->name ?? '-' }}</td>
                    <td>{{ match($ticket->category) {
                        'incident_management' => 'Manajemen Insiden',
                        'service_request'     => 'Permintaan Layanan',
                        'user_support'        => 'Dukungan Pengguna',
                        'access_management'   => 'Manajemen Akses',
                        'asset_management'    => 'Manajemen Aset',
                        'change_management'   => 'Manajemen Perubahan',
                        'network_support'     => 'Dukungan Jaringan',
                        'security_support'    => 'Dukungan Keamanan',
                        'documentation_kb'    => 'Dokumentasi & Basis Pengetahuan',
                        default => $ticket->category,
                    } }}</td>
                    <td>
                        <span class="badge badge-{{ $ticket->priority }}">
                            {{ match($ticket->priority) {
                                'critical' => 'Kritis',
                                'high' => 'Tinggi',
                                'medium' => 'Sedang',
                                'low' => 'Rendah',
                                default => $ticket->priority,
                            } }}
                        </span>
                    </td>
                    <td>{{ Str::limit($ticket->subject, 40) }}</td>
                    <td>
                        <span class="badge badge-{{ $ticket->status }}">
                            {{ match($ticket->status) {
                                'open' => 'Dibuka',
                                'in_progress' => 'Diproses',
                                'waiting_for_user' => 'Menunggu',
                                'resolved' => 'Selesai',
                                'closed' => 'Ditutup',
                                default => $ticket->status,
                            } }}
                        </span>
                    </td>
                    <td>{{ $ticket->assignedTo->name ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center;">Tidak ada data tiket</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <p style="margin-top: 10px; font-size: 11px; color: #666;">Total: {{ $tickets->count() }} tiket</p>
    </div>

    <div class="footer">
        <p>Dokumen ini dihasilkan secara otomatis oleh Sistem IT Helpdesk</p>
    </div>
</body>
</html>
