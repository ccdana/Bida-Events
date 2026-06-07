<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\Invitation;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class RsvpController extends Controller
{
    public function confirm(Request $request, string $slug, string $token)
    {
        $invitation = Invitation::where('slug', $slug)->where('status', 'active')->firstOrFail();

        $guest = Guest::where('invitation_id', $invitation->id)
            ->where('qr_code_token', $token)
            ->firstOrFail();

        $validated = $request->validate([
            'status' => ['required', 'in:confirmed,declined'],
            'passes_confirmed' => ['nullable', 'integer', 'min:0'],
            'dietary_restrictions' => ['nullable', 'string', 'max:500'],
        ]);

        if ($validated['status'] === 'confirmed') {
            $passes = min(
                (int) ($validated['passes_confirmed'] ?? 1),
                $guest->passes_allocated
            );
            $passes = max(1, $passes);

            $guest->update([
                'status' => 'confirmed',
                'passes_confirmed' => $passes,
                'dietary_restrictions' => $validated['dietary_restrictions'] ?? null,
                'confirmed_at' => now(),
            ]);
        } else {
            $guest->update([
                'status' => 'declined',
                'passes_confirmed' => 0,
                'dietary_restrictions' => null,
                'confirmed_at' => now(),
            ]);
        }

        if ($request->wantsJson()) {
            $qrSvg = null;
            if ($guest->status === 'confirmed') {
                $qrSvg = QrCode::size(200)->margin(1)->generate($guest->qr_code_token);
            }

            return response()->json([
                'success' => true,
                'status' => $guest->status,
                'passes_confirmed' => $guest->passes_confirmed,
                'qr_svg' => $qrSvg ? (string) $qrSvg : null,
                'guest_name' => $guest->name,
            ]);
        }

        return redirect()
            ->route('invitation.guest', ['slug' => $slug, 'token' => $token])
            ->with('rsvp_done', true);
    }
}
