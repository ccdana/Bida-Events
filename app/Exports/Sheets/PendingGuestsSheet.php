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

/** Hoja "Por contactar": invitados sin respuesta, primero los que tienen más pases. */
class PendingGuestsSheet extends ReportSheet implements FromArray, WithColumnWidths, WithEvents, WithHeadings, WithStrictNullComparison, WithTitle
{
    public function title(): string
    {
        return 'Por contactar';
    }

    public function headings(): array
    {
        return ['Invitado', 'Teléfono', 'Pases asignados', 'WhatsApp', 'Enlace personal'];
    }

    public function columnWidths(): array
    {
        return ['A' => 32, 'B' => 16, 'C' => 14, 'D' => 16, 'E' => 46];
    }

    public function array(): array
    {
        return $this->report['groups']['pending']->map(fn (array $row) => [
            $row['name'],
            $row['phone'] ?? '',
            $row['allocated'],
            $row['whatsapp'] ? 'Escribir' : 'Sin teléfono',
            $row['link'],
        ])->all();
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $pending = $this->report['groups']['pending']->values();
                $last = $pending->count() + 1;

                $this->styleTableHeader($sheet, 'A1:E1');
                $sheet->getRowDimension(1)->setRowHeight(26);
                $sheet->freezePane('A2');
                $this->styleBody($sheet, "A2:E{$last}");
                $sheet->getStyle("C2:C{$last}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                foreach ($pending as $index => $row) {
                    $line = $index + 2;
                    $this->link($sheet, "D{$line}", $row['whatsapp']);
                    $this->link($sheet, "E{$line}", $row['link']);
                }
            },
        ];
    }
}
