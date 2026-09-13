<?php

namespace App\Exports;

use App\Exports\Sheets\DietarySheet;
use App\Exports\Sheets\GuestListSheet;
use App\Exports\Sheets\PendingGuestsSheet;
use App\Exports\Sheets\SummarySheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Reporte de invitados en Excel: resumen para decidir, listado completo con
 * filtros, invitados por contactar y restricciones alimentarias.
 */
class GuestReportExport implements WithMultipleSheets
{
    public function __construct(private readonly array $report) {}

    public function sheets(): array
    {
        return array_values(array_filter([
            new SummarySheet($this->report),
            new GuestListSheet($this->report),
            $this->report['groups']['pending']->isNotEmpty() ? new PendingGuestsSheet($this->report) : null,
            $this->report['dietary']->isNotEmpty() ? new DietarySheet($this->report) : null,
        ]));
    }
}
