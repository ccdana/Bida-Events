<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

/**
 * Hoja "Invitados": el listado completo con filtros y el enlace personal de cada uno.
 * Solo las columnas que el cliente usa para organizar la fiesta; lo demás está en "Resumen".
 */
class GuestListSheet extends ReportSheet implements FromArray, WithColumnWidths, WithEvents, WithHeadings, WithStrictNullComparison, WithTitle
{
    public function title(): string
    {
        return 'Invitados';
    }

    public function headings(): array
    {
        return ['Invitado', 'Teléfono', 'Estado', 'Pases', 'Personas que vienen', 'Mesa', 'Alimentación', 'Su enlace'];
    }

    public function columnWidths(): array
    {
        return ['A' => 34, 'B' => 16, 'C' => 15, 'D' => 8, 'E' => 14, 'F' => 9, 'G' => 34, 'H' => 48];
    }

    public function array(): array
    {
        return $this->report['rows']->map(fn (array $row) => [
            $row['name'],
            $row['phone'] ?? '',
            $row['statusLabel'],
            $row['allocated'],
            $row['status'] === 'confirmed' ? $row['confirmed'] : '',
            $row['table'] ?? '',
            $row['dietary'] ?? '',
            $row['link'],
        ])->all();
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $last = $this->report['rows']->count() + 1;

                $this->styleTableHeader($sheet, 'A1:H1');
                $sheet->getRowDimension(1)->setRowHeight(30);
                // El nombre queda a la vista al desplazarse y cada columna se puede filtrar
                $sheet->freezePane('B2');
                $sheet->setAutoFilter("A1:H{$last}");

                if ($last < 2) {
                    return;
                }

                $this->styleBody($sheet, "A2:H{$last}");
                $sheet->getStyle("D2:F{$last}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                foreach ($this->report['rows']->values() as $index => $row) {
                    $line = $index + 2;
                    $sheet->getStyle("C{$line}")->applyFromArray($this->statusStyle($row['status']));
                    $this->link($sheet, "H{$line}", $row['link']);
                }
            },
        ];
    }
}
