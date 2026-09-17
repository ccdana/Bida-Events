<?php

namespace App\Modules\Invitation;

use App\Models\Invitation;
use App\Models\InvitationGiftOption;
use App\Modules\Concerns\HasSectionTexts;
use App\Modules\Concerns\ReadsValues;
use App\Modules\Concerns\ReplacesOrderedRows;
use App\Modules\Module;

/**
 * Regalos: opciones de la lista, lluvia de sobres y tienda externa (invitation_gift_options,
 * cada una con su tipo), la cuenta bancaria (invitation_bank_accounts) y el título en la sección.
 */
class GiftsModule extends Module
{
    use HasSectionTexts, ReadsValues, ReplacesOrderedRows;

    public function code(): string
    {
        return 'regalos';
    }

    public function label(): string
    {
        return 'Regalos';
    }

    public function defaults(): array
    {
        return [
            'sobres' => ['titulo' => '', 'direccion' => ''],
            'banco' => ['banco' => '', 'titular' => '', 'ci' => '', 'cuenta' => '', 'qr_url' => ''],
            'titulo' => '',
            'tienda_url' => '',
            'tienda_texto' => '',
            'opciones' => [],
        ];
    }

    public function relations(): array
    {
        return ['giftOptions', 'bankAccounts', 'sections.feature'];
    }

    public function load(Invitation $invitation): array
    {
        $options = $invitation->giftOptions;
        $store = $options->firstWhere('type', InvitationGiftOption::TYPE_STORE);
        $envelope = $options->firstWhere('type', InvitationGiftOption::TYPE_ENVELOPE);
        $bank = $invitation->bankAccounts->first();

        return [
            'titulo' => $this->section($invitation)?->title ?? '',
            'tienda_url' => $store?->url ?? '',
            'tienda_texto' => $store?->title ?? '',
            'opciones' => $options
                ->where('type', InvitationGiftOption::TYPE_OPTION)
                ->map(fn (InvitationGiftOption $option) => $this->compact([
                    'titulo' => $option->title,
                    'descripcion' => $option->description,
                    'enlace' => $option->url,
                    'imagen' => $option->image_url,
                ]))
                ->values()
                ->all(),
            'sobres' => [
                'titulo' => $envelope?->title ?? '',
                'direccion' => $envelope?->address ?? '',
            ],
            'banco' => [
                'banco' => $bank?->bank_name ?? '',
                'titular' => $bank?->holder ?? '',
                'ci' => $bank?->document_id ?? '',
                'cuenta' => $bank?->account_number ?? '',
                'qr_url' => $bank?->qr_image_url ?? '',
            ],
        ];
    }

    public function save(Invitation $invitation, array $data): void
    {
        $rows = [];

        foreach ($this->list($data['opciones'] ?? null) as $option) {
            if (! is_array($option)) {
                continue;
            }

            $rows[] = $this->row(InvitationGiftOption::TYPE_OPTION, [
                'title' => $this->text($option['titulo'] ?? null, 255),
                'description' => $this->text($option['descripcion'] ?? null),
                'url' => $this->text($option['enlace'] ?? null),
                'image_url' => $this->text($option['imagen'] ?? null),
            ]);
        }

        $envelope = $this->items($data['sobres'] ?? null);

        if ($this->filled($envelope['titulo'] ?? null) || $this->filled($envelope['direccion'] ?? null)) {
            $rows[] = $this->row(InvitationGiftOption::TYPE_ENVELOPE, [
                'title' => $this->text($envelope['titulo'] ?? null, 255),
                'address' => $this->text($envelope['direccion'] ?? null, 500),
            ]);
        }

        if ($this->filled($data['tienda_url'] ?? null) || $this->filled($data['tienda_texto'] ?? null)) {
            $rows[] = $this->row(InvitationGiftOption::TYPE_STORE, [
                'title' => $this->text($data['tienda_texto'] ?? null, 255),
                'url' => $this->text($data['tienda_url'] ?? null),
            ]);
        }

        $this->replaceOrdered($invitation->giftOptions(), $rows);

        $bank = $this->items($data['banco'] ?? null);
        $account = [
            'bank_name' => $this->text($bank['banco'] ?? null, 255),
            'holder' => $this->text($bank['titular'] ?? null, 255),
            'document_id' => $this->text($bank['ci'] ?? null, 50),
            'account_number' => $this->text($bank['cuenta'] ?? null, 100),
            'qr_image_url' => $this->text($bank['qr_url'] ?? null),
        ];

        $this->replaceOrdered(
            $invitation->bankAccounts(),
            array_filter($account, fn ($value) => $value !== null) === [] ? [] : [$account],
        );

        $this->saveSection($invitation, ['title' => $this->text($data['titulo'] ?? null, 255)]);
    }

    public function hasContent(array $data): bool
    {
        $envelope = $this->items($data['sobres'] ?? null);
        $bank = $this->items($data['banco'] ?? null);

        return $this->filled($data['titulo'] ?? null)
            || $this->filled($data['tienda_url'] ?? null)
            || $this->filled($data['opciones'] ?? [])
            || $this->filled($envelope['titulo'] ?? null)
            || $this->filled($envelope['direccion'] ?? null)
            || array_filter($bank, fn ($value) => $this->filled($value)) !== [];
    }

    /** Todas las columnas en cada fila: al reutilizar una fila de otro tipo no quedan restos. */
    protected function row(string $type, array $values): array
    {
        return array_merge([
            'type' => $type,
            'title' => null,
            'description' => null,
            'url' => null,
            'address' => null,
            'image_url' => null,
        ], $values);
    }
}
