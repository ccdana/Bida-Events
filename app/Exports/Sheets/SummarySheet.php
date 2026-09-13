<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

/** Hoja "Resumen": cifras con su explicación, qué hacer ahora y distribución por mesa. */
class SummarySheet extends ReportSheet implements FromArray, WithColumnWidths, WithEvents, WithStrictNullComparison, WithTitle
{
    private array $sectionRows = [];

    private array $headerRows = [];

    private array $bodyRanges = [];

    private array $actionRows = [];

    private ?int $percentRow = null;

    public function title(): string
    {
        return 'Resumen';
    }

    public function columnWidths(): array
    {
        return ['A' => 32, 'B' => 18, 'C' => 64];
    }

    public function array(): array
    {
        $stats = $this->report['stats'];
        $daysLeft = $this->report['daysLeft'];

        $rows = [
            [config('bida.brand').' | Reporte de invitados'],
            [$this->report['invitation']->title],
            [$this->report['eventDateLabel']],
            ['Generado el '.$this->report['generatedAt']],
            [],
        ];

        $this->addSection($rows, 'Resumen', ['Indicador', 'Valor', 'Qué significa']);
        $from = count($rows) + 1;

        foreach ([
            ['Invitados registrados', $stats['totalGuests'], 'Familias o personas que recibieron un enlace personal.'],
            ['Respondieron', $stats['responseRate'] / 100, "{$stats['respondedGuests']} de {$stats['totalGuests']} invitados ya confirmaron o avisaron que no irán."],
            ['Personas confirmadas', $stats['confirmedPeople'], 'Base para comida, sillas, mesas y recuerdos.'],
            ['Personas por confirmar', $stats['pendingPeople'], 'Pases de los invitados que todavía no responden.'],
            ['Asistencia máxima posible', $stats['maxPeople'], 'Si todos los pendientes asisten con todos sus pases.'],
            ['Invitados sin responder', $stats['pendingGuests'], 'Contáctalos: están en la hoja "Por contactar".'],
            ['No asistirán', $stats['declinedGuests'], 'Invitados que avisaron que no irán.'],
            ['Pases libres', $stats['releasedPasses'], 'Lugares que puedes reasignar a otros invitados.'],
            ['Días para el evento', $daysLeft !== null && $daysLeft >= 0 ? $daysLeft : 'El evento ya pasó', 'Tiempo para cerrar la lista con el lugar y el catering.'],
        ] as $index => $kpi) {
            $rows[] = $kpi;

            if ($index === 1) {
                $this->percentRow = count($rows);
            }
        }

        $this->bodyRanges[] = [$from, count($rows)];

        $rows[] = [];
        $this->sectionRows[] = count($rows) + 1;
        $rows[] = ['Qué hacer ahora'];

        foreach ($this->report['actions'] as $action) {
            $rows[] = [$action];
            $this->actionRows[] = count($rows);
        }

        if ($this->report['tables']->isNotEmpty()) {
            $rows[] = [];
            $this->addSection($rows, 'Mesas', ['Mesa', 'Invitados confirmados', 'Personas']);
            $from = count($rows) + 1;

            foreach ($this->report['tables'] as $table) {
                $rows[] = [$table['table'], $table['guests'], $table['people']];
            }

            $this->bodyRanges[] = [$from, count($rows)];
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->setShowGridlines(false);

                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->getColor()->setARGB(self::GOLD);
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(13);
                $sheet->getStyle('A3:A4')->getFont()->getColor()->setARGB(self::MUTED);

                foreach ($this->sectionRows as $row) {
                    $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(12);
                    $sheet->getRowDimension($row)->setRowHeight(24);
                }

                foreach ($this->headerRows as $row) {
                    $this->styleTableHeader($sheet, "A{$row}:C{$row}");
                }

                foreach ($this->bodyRanges as [$from, $to]) {
                    $this->styleBody($sheet, "A{$from}:C{$to}");
                    $sheet->getStyle("B{$from}:B{$to}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 12],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                    ]);
                    $sheet->getStyle("C{$from}:C{$to}")->getFont()->getColor()->setARGB(self::MUTED);
                }

                if ($this->percentRow) {
                    $sheet->getStyle("B{$this->percentRow}")->getNumberFormat()->setFormatCode('0%');
                }

                foreach ($this->actionRows as $row) {
                    $sheet->mergeCells("A{$row}:C{$row}");
                    $sheet->getStyle("A{$row}")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
                    $length = mb_strlen((string) $sheet->getCell("A{$row}")->getValue());
                    $sheet->getRowDimension($row)->setRowHeight(max(18, (int) ceil($length / 105) * 16));
                }
            },
        ];
    }

    private function addSection(array &$rows, string $title, array $headers): void
    {
        $this->sectionRows[] = count($rows) + 1;
        $rows[] = [$title];
        $this->headerRows[] = count($rows) + 1;
        $rows[] = $headers;
    }
}
