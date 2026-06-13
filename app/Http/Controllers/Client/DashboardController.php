<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Select específico + with() para eager loading + pagination
        $invitations = auth()->user()
            ->invitations()
            ->select('id', 'user_id', 'event_type_id', 'slug', 'title', 'event_date', 'status', 'created_at')
            ->with([
                'eventType:id,name,slug',
                'guests:id,invitation_id,status' // Solo los campos necesarios
            ])
            ->withCount(['guests', 'contributions'])
            ->latest()
            ->paginate(15); // Paginación en lugar de traer todo

        return view('client.dashboard', compact('invitations'));
    }

    public function show(Invitation $invitation)
    {
        abort_unless(auth()->id() === $invitation->user_id, 403);

        // Cargar con select específico y usar SQL aggregates
        $invitation->loadCount('guests')
                   ->loadAggregate('guests', 'status', 'confirmed')
                   ->loadAggregate('guests', 'status', 'declined')
                   ->loadAggregate('guests', 'status', 'pending');

        // Usar raw queries para los conteos que ya están cargados
        $guests = $invitation->guests()
            ->select('id', 'invitation_id', 'name', 'status', 'passes_confirmed')
            ->orderBy('name')
            ->get();

        // Agrupar por status después de cargar (en memoria, no en BD)
        $confirmed = $guests->where('status', 'confirmed');
        $declined = $guests->where('status', 'declined');
        $pending = $guests->where('status', 'pending');
        $totalPasses = $confirmed->sum('passes_confirmed');

        return view('client.invitation', compact(
            'invitation',
            'guests',
            'confirmed',
            'declined',
            'pending',
            'totalPasses'
        ));
    }
}
