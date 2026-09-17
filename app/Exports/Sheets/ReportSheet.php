<?php

namespace App\Exports\Sheets;

use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/** Colores de la marca y estilos compartidos por las hojas del reporte de invitados. */
abstract class ReportSheet
{
    protected const INK = 'FF1D1E20';

    protected const MUTED = 'FF6C6E73';

    protected const LINE = 'FFE3E4E0';

    protected const GOLD = 'FF8A6A1C';

    protected const GOLD_TINT = 'FFF3EAD4';

    protected const DANGER = 'FF9B2C22';

    protected const DANGER_TINT = 'FFF6E3E1';

    protected const NEUTRAL_TINT = 'FFEFEFEC';

    public function __construct(protected array $report) {}

    protected function styleTableHeader(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::INK]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ]);
    }

    protected function styleBody(Worksheet $sheet, string $range): void
    {
        $line = ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => self::LINE]];

        $sheet->getStyle($range)->applyFromArray([
            'borders' => ['horizontal' => $line, 'bottom' => $line],
            'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'wrapText' => true],
        ]);
    }

    protected function statusStyle(string $status): array
    {
        [$fill, $color] = match ($status) {
            'confirmed' => [self::GOLD_TINT, self::GOLD],
            'declined' => [self::DANGER_TINT, self::DANGER],
            default => [self::NEUTRAL_TINT, self::MUTED],
        };

        return [
            'font' => ['bold' => true, 'color' => ['argb' => $color]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $fill]],
        ];
    }

    protected function link(Worksheet $sheet, string $cell, ?string $url): void
    {
        if (! $url) {
            return;
        }

        $sheet->getCell($cell)->getHyperlink()->setUrl($url);
        $sheet->getStyle($cell)->getFont()->setUnderline(true)->getColor()->setARGB(self::GOLD);
    }
}
