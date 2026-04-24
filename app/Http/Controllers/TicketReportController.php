<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TicketReportController extends Controller
{
    public function exportExcel(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));

        $tickets = Ticket::with(['user', 'assignedTo'])
            ->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay(),
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get statistics
        $stats = $this->getStatistics($startDate, $endDate);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Tiket');

        // Title
        $sheet->setCellValue('A1', 'LAPORAN TIKET HELPDESK');
        $sheet->mergeCells('A1:J1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Period
        $periodLabel = Carbon::parse($startDate)->translatedFormat('d M Y') . ' - ' . Carbon::parse($endDate)->translatedFormat('d M Y');
        $sheet->setCellValue('A2', 'Periode: ' . $periodLabel);
        $sheet->mergeCells('A2:J2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Statistics
        $sheet->setCellValue('A4', 'Ringkasan Statistik');
        $sheet->getStyle('A4')->getFont()->setBold(true)->setSize(12);

        $sheet->setCellValue('A5', 'Total Tiket');
        $sheet->setCellValue('B5', $stats['total']);
        $sheet->setCellValue('C5', 'Dibuka');
        $sheet->setCellValue('D5', $stats['open']);
        $sheet->setCellValue('E5', 'Diproses');
        $sheet->setCellValue('F5', $stats['in_progress']);
        $sheet->setCellValue('G5', 'Selesai');
        $sheet->setCellValue('H5', $stats['resolved'] + $stats['closed']);
        $sheet->setCellValue('I5', 'Tingkat Selesai');
        $sheet->setCellValue('J5', $stats['resolution_rate'] . '%');

        $sheet->getStyle('A5:J5')->getFont()->setBold(true);

        // Header row
        $headers = [
            'No. Tiket',
            'Tanggal Dibuat',
            'Pelapor',
            'Kategori',
            'Prioritas',
            'Subjek',
            'Status',
            'Ditugaskan Ke',
            'Tanggal Selesai',
            'Waktu Penyelesaian (Hari)',
        ];

        $headerRow = 7;
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $headerRow, $header);
            $col++;
        }

        // Style header
        $sheet->getStyle('A' . $headerRow . ':J' . $headerRow)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F46E5'],
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Data rows
        $row = $headerRow + 1;
        foreach ($tickets as $ticket) {
            $resolutionDays = '-';
            if ($ticket->resolved_at) {
                $days = (int) $ticket->created_at->diffInDays($ticket->resolved_at);
                $resolutionDays = $days < 1 ? 1 : $days; // Minimal 1 hari
            }

            $sheet->setCellValue('A' . $row, $ticket->ticket_number);
            $sheet->setCellValue('B' . $row, $ticket->created_at->format('d/m/Y H:i'));
            $sheet->setCellValue('C' . $row, $ticket->user->name ?? '-');
            $sheet->setCellValue('D' . $row, $this->getCategoryLabel($ticket->category));
            $sheet->setCellValue('E' . $row, $this->getPriorityLabel($ticket->priority));
            $sheet->setCellValue('F' . $row, $ticket->subject);
            $sheet->setCellValue('G' . $row, $this->getStatusLabel($ticket->status));
            $sheet->setCellValue('H' . $row, $ticket->assignedTo->name ?? '-');
            $sheet->setCellValue('I' . $row, $ticket->resolved_at ? $ticket->resolved_at->format('d/m/Y H:i') : '-');
            $sheet->setCellValue('J' . $row, $resolutionDays);

            $row++;
        }

        // Add borders to data
        $lastRow = $row - 1;
        if ($lastRow >= $headerRow) {
            $sheet->getStyle('A' . $headerRow . ':J' . $lastRow)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ]);
        }

        // Auto-size columns
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Generate file
        $filename = 'laporan-tiket-' . $startDate . '-to-' . $endDate . '.xlsx';

        $writer = new Xlsx($spreadsheet);

        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
        ];

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, $headers);
    }

    public function exportPdf(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));

        $tickets = Ticket::with(['user', 'assignedTo'])
            ->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay(),
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        // Statistics
        $stats = $this->getStatistics($startDate, $endDate);
        $categoryStats = $this->getCategoryStatistics($startDate, $endDate);
        $priorityStats = $this->getPriorityStatistics($startDate, $endDate);

        return view('reports.ticket-pdf', [
            'tickets' => $tickets,
            'stats' => $stats,
            'categoryStats' => $categoryStats,
            'priorityStats' => $priorityStats,
            'startDate' => Carbon::parse($startDate)->translatedFormat('d M Y'),
            'endDate' => Carbon::parse($endDate)->translatedFormat('d M Y'),
        ]);
    }

    private function getStatistics($startDate, $endDate): array
    {
        $query = Ticket::whereBetween('created_at', [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay(),
        ]);

        $total = (clone $query)->count();
        $open = (clone $query)->where('status', 'open')->count();
        $inProgress = (clone $query)->where('status', 'in_progress')->count();
        $resolved = (clone $query)->where('status', 'resolved')->count();
        $closed = (clone $query)->where('status', 'closed')->count();

        return [
            'total' => $total,
            'open' => $open,
            'in_progress' => $inProgress,
            'resolved' => $resolved,
            'closed' => $closed,
            'resolution_rate' => $total > 0 ? round((($resolved + $closed) / $total) * 100, 1) : 0,
        ];
    }

    private function getCategoryStatistics($startDate, $endDate): array
    {
        return Ticket::whereBetween('created_at', [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay(),
        ])
            ->selectRaw('category, count(*) as total')
            ->groupBy('category')
            ->pluck('total', 'category')
            ->toArray();
    }

    private function getPriorityStatistics($startDate, $endDate): array
    {
        return Ticket::whereBetween('created_at', [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay(),
        ])
            ->selectRaw('priority, count(*) as total')
            ->groupBy('priority')
            ->pluck('total', 'priority')
            ->toArray();
    }

    private function getCategoryLabel(string $category): string
    {
        return match ($category) {
            'incident_management' => 'Incident Management',
            'service_request'     => 'Service Request',
            'user_support'        => 'User Support',
            'access_management'   => 'Access Management',
            'asset_management'    => 'Asset Management',
            'change_management'   => 'Change Management',
            'network_support'     => 'Network Support',
            'security_support'    => 'Security Support',
            'documentation_kb'    => 'Documentation & Knowledge Base',
            default => $category,
        };
    }

    private function getPriorityLabel(string $priority): string
    {
        return match ($priority) {
            'critical' => 'Kritis',
            'high' => 'Tinggi',
            'medium' => 'Sedang',
            'low' => 'Rendah',
            default => $priority,
        };
    }

    private function getStatusLabel(string $status): string
    {
        return match ($status) {
            'open' => 'Dibuka',
            'in_progress' => 'Diproses',
            'waiting_for_user' => 'Menunggu User',
            'resolved' => 'Selesai',
            'closed' => 'Ditutup',
            default => $status,
        };
    }
}
