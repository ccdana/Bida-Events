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

/** Hoja "Alimentación": restricciones de los invitados confirmados, lista para el catering. */
class DietarySheet extends ReportSheet implements FromArray, WithColumnWidths, WithEvents, WithHeadings, WithStrictNullComparison, WithTitle
{
    public function title(): string
    {
        return 'Alimentación';
    }

    public function headings(): array
    {
        return ['Invitado', 'Personas', 'Mesa', 'Restricción alimentaria'];
    }

    public function columnWidths(): array
    {
        return ['A' => 32, 'B' => 11, 'C' => 9, 'D' => 60];
    }

    public function array(): array
    {
        return $this->report['dietary']->map(fn (array $row) => [
            $row['name'],
            $row['confirmed'],
            $row['table'] ?? '',
            $row['dietary'],
        ])->all();
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $last = $this->report['dietary']->count() + 1;

                $this->styleTableHeader($sheet, 'A1:D1');
                $sheet->getRowDimension(1)->setRowHeight(26);
                $sheet->freezePane('A2');
                $this->styleBody($sheet, "A2:D{$last}");
                $sheet->getStyle("B2:C{$last}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            },
        ];
    }
}
