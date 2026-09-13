<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

/** Hoja "Invitados": listado completo con filtros, estados coloreados y enlaces personales. */
class GuestListSheet extends ReportSheet implements FromArray, WithColumnWidths, WithEvents, WithHeadings, WithStrictNullComparison, WithTitle
{
    public function title(): string
    {
        return 'Invitados';
    }

    public function headings(): array
    {
        return ['Invitado', 'Teléfono', 'Estado', 'Pases asignados', 'Personas confirmadas', 'Pases libres', 'Mesa', 'Restricciones alimentarias', 'Confirmó el', 'Enlace personal'];
    }

    public function columnWidths(): array
    {
        return ['A' => 32, 'B' => 16, 'C' => 14, 'D' => 11, 'E' => 13, 'F' => 10, 'G' => 9, 'H' => 32, 'I' => 17, 'J' => 46];
    }

    public function array(): array
    {
        return $this->report['rows']->map(fn (array $row) => [
            $row['name'],
            $row['phone'] ?? '',
            $row['statusLabel'],
            $row['allocated'],
            $row['confirmed'],
            $row['free'],
            $row['table'] ?? '',
            $row['dietary'] ?? '',
            $row['confirmedAt'] ? Date::dateTimeToExcel($row['confirmedAt']) : '',
            $row['link'],
        ])->all();
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $last = $this->report['rows']->count() + 1;

                $this->styleTableHeader($sheet, 'A1:J1');
                $sheet->getRowDimension(1)->setRowHeight(30);
                $sheet->freezePane('B2');
                $sheet->setAutoFilter("A1:J{$last}");

                if ($last < 2) {
                    return;
                }

                $this->styleBody($sheet, "A2:J{$last}");
                $sheet->getStyle("D2:G{$last}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("I2:I{$last}")->getNumberFormat()->setFormatCode('dd/mm/yyyy hh:mm');

                foreach ($this->report['rows']->values() as $index => $row) {
                    $line = $index + 2;
                    $sheet->getStyle("C{$line}")->applyFromArray($this->statusStyle($row['status']));
                    $this->link($sheet, "J{$line}", $row['link']);
                }
            },
        ];
    }
}
