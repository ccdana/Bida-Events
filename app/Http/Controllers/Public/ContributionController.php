<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\GuestContribution;
use App\Models\Invitation;
use App\Models\PollVote;
use App\Services\MediaUploadService;
use Illuminate\Http\Request;

class ContributionController extends Controller
{
    public function __construct(
        protected MediaUploadService $mediaUpload
    ) {}
    public function listSongs(string $slug)
    {
        $invitation = Invitation::where('slug', $slug)->where('status', 'active')->firstOrFail();

        $songs = $invitation->contributions()
            ->where('type', 'song_request')
            ->with('guest:id,name')
            ->latest('created_at')
            ->take(50)
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'text' => $c->content_text,
                'guest' => $c->guest?->name,
                'at' => $c->created_at?->diffForHumans(),
            ]);

        return response()->json(['songs' => $songs]);
    }

    public function storeSong(Request $request, string $slug)
    {
        $invitation = Invitation::where('slug', $slug)->where('status', 'active')->firstOrFail();

        $validated = $request->validate([
            'content_text' => ['required', 'string', 'max:500'],
            'guest_token' => ['nullable', 'string'],
        ]);

        $guestId = null;
        if (! empty($validated['guest_token'])) {
            $guest = Guest::where('invitation_id', $invitation->id)
                ->where('qr_code_token', $validated['guest_token'])
                ->first();
            $guestId = $guest?->id;
        }

        GuestContribution::create([
            'invitation_id' => $invitation->id,
            'guest_id' => $guestId,
            'type' => 'song_request',
            'content_text' => $validated['content_text'],
            'created_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => '¡Canción agregada a la playlist!']);
    }

    public function listPhotos(string $slug)
    {
        $invitation = Invitation::where('slug', $slug)->where('status', 'active')->firstOrFail();

        $photos = $invitation->contributions()
            ->where('type', 'live_photo')
            ->with('guest:id,name')
            ->latest('created_at')
            ->take(60)
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'url' => $c->file_path,
                'guest' => $c->guest?->name,
                'at' => $c->created_at?->diffForHumans(),
            ]);

        return response()->json(['photos' => $photos]);
    }

    public function storePhoto(Request $request, string $slug)
    {
        $invitation = Invitation::where('slug', $slug)->where('status', 'active')->firstOrFail();

        $validated = $request->validate([
            'photo' => ['required', 'image', 'max:5120'],
            'guest_token' => ['nullable', 'string'],
        ]);

        $guestToken = $validated['guest_token'] ?? null;
        $guestId = null;
        if ($guestToken) {
            $guest = Guest::where('invitation_id', $invitation->id)
                ->where('qr_code_token', $guestToken)
                ->first();
            $guestId = $guest?->id;
        }

        $this->mediaUpload->validateFile($request->file('photo'), 'image');
        $upload = $this->mediaUpload->upload(
            $request->file('photo'),
            'image',
            $invitation->slug,
            'fotomural'
        );

        GuestContribution::create([
            'invitation_id' => $invitation->id,
            'guest_id' => $guestId,
            'type' => 'live_photo',
            'file_path' => $upload['url'],
            'created_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => '¡Foto compartida al fotomural!']);
    }

    public function votePoll(Request $request, string $slug, string $pollId)
    {
        $invitation = Invitation::where('slug', $slug)->where('status', 'active')->firstOrFail();

        $validated = $request->validate([
            'option_index' => ['required', 'integer', 'min:0'],
            'guest_token' => ['nullable', 'string'],
        ]);

        $voterKey = $validated['guest_token'] ?? $request->session()->getId();

        $existing = PollVote::where('invitation_id', $invitation->id)
            ->where('poll_id', $pollId)
            ->where('voter_key', $voterKey)
            ->first();

        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Ya votaste en esta encuesta.'], 422);
        }

        $guestId = null;
        if (! empty($validated['guest_token'])) {
            $guest = Guest::where('invitation_id', $invitation->id)
                ->where('qr_code_token', $validated['guest_token'])
                ->first();
            $guestId = $guest?->id;
        }

        PollVote::create([
            'invitation_id' => $invitation->id,
            'poll_id' => $pollId,
            'option_index' => $validated['option_index'],
            'guest_id' => $guestId,
            'voter_key' => $voterKey,
            'created_at' => now(),
        ]);

        $modulos = $invitation->modulesData()->where('feature_code', 'encuestas')->first();
        $poll = collect($modulos?->json_data['preguntas'] ?? [])->firstWhere('id', $pollId);
        $percentages = app(\App\Services\InvitationModuleService::class)->pollResults(
            $invitation,
            $pollId,
            count($poll['opciones'] ?? [])
        );

        return response()->json(['success' => true, 'percentages' => $percentages]);
    }
}
