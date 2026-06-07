<?php

namespace App\Exports;

use App\Models\Invitation;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class GuestsExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        protected Invitation $invitation
    ) {}

    public function collection()
    {
        return $this->invitation->guests()->orderBy('name')->get();
    }

    public function headings(): array
    {
        return [
            'Nombre',
            'Teléfono',
            'Pases Asignados',
            'Pases Confirmados',
            'Estado',
            'Restricciones Alimentarias',
            'Mesa',
            'Confirmado el',
        ];
    }

    public function map($guest): array
    {
        return [
            $guest->name,
            $guest->phone ?? '',
            $guest->passes_allocated,
            $guest->passes_confirmed,
            match ($guest->status) {
                'confirmed' => 'Confirmado',
                'declined' => 'No asistirá',
                default => 'Pendiente',
            },
            $guest->dietary_restrictions ?? '',
            $guest->table_number ?? '',
            $guest->confirmed_at?->format('d/m/Y H:i') ?? '',
        ];
    }
}
