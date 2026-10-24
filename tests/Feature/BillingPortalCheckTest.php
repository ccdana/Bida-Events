<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\Concerns\LemonSqueezyPayloads;
use Tests\TestCase;

/**
 * «Administrar mi suscripción»: la pasarela da un enlace firmado (entra al portal sin pedir iniciar sesión) o uno sin
 * firma (manda al ingreso por correo de Lemon Squeezy). Lo segundo queda anotado, y el comando de diagnóstico dice cuál
 * llega sin mostrar la firma.
 */
class BillingPortalCheckTest extends TestCase
{
    use LemonSqueezyPayloads, RefreshDatabase;

    private function client(): User
    {
        return User::factory()->reseller('inicial', now()->addDays(10)->toDateString())->create([
            'username' => 'ana.quispe', 'payment_provider' => 'lemonsqueezy', 'provider_subscription_id' => '3001',
        ]);
    }

    private function portalReturns(string $url): void
    {
        $this->configureLemonSqueezy();
        Http::fake(['api.lemonsqueezy.com/v1/subscriptions/*' => Http::response(['data' => ['attributes' => ['urls' => ['customer_portal' => $url]]]])]);
    }

    public function test_the_command_says_a_signed_link_will_not_ask_to_log_in_and_never_prints_the_signature(): void
    {
        $this->client();
        $this->portalReturns('https://bida-events.lemonsqueezy.com/billing?expires='.now()->addDay()->timestamp.'&user=1&signature=SECRETA123');

        $this->artisan('bida:portal-de-pagos', ['usuario' => 'Ana.Quispe'])
            ->expectsOutputToContain('bida-events.lemonsqueezy.com/billing')
            ->expectsOutputToContain('Firmado')
            ->expectsOutputToContain('sin pedir iniciar sesión')
            ->doesntExpectOutputToContain('SECRETA123')
            ->assertSuccessful();
    }

    public function test_the_command_warns_when_the_link_comes_without_a_signature(): void
    {
        $this->client();
        $this->portalReturns('https://bida-events.lemonsqueezy.com/billing');

        $this->artisan('bida:portal-de-pagos', ['usuario' => 'ana.quispe'])
            ->expectsOutputToContain('Sin firma')
            ->assertFailed();
    }

    public function test_the_command_handles_a_client_without_a_card_subscription_and_an_unknown_one(): void
    {
        $this->configureLemonSqueezy();
        User::factory()->create(['username' => 'sin.tarjeta']);

        $this->artisan('bida:portal-de-pagos', ['usuario' => 'sin.tarjeta'])->expectsOutputToContain('no tiene una suscripción con tarjeta')->assertSuccessful();
        $this->artisan('bida:portal-de-pagos', ['usuario' => 'nadie'])->expectsOutputToContain('No hay un cliente')->assertFailed();
    }

    public function test_an_unsigned_link_is_logged_but_the_client_is_still_sent_there(): void
    {
        $user = $this->client();
        $this->portalReturns('https://bida-events.lemonsqueezy.com/billing');

        Log::shouldReceive('warning')->once()->withArgs(fn (string $message, array $context) => str_contains($message, 'sin firma') && $context['host'] === 'bida-events.lemonsqueezy.com');

        $this->actingAs($user)->get(route('client.subscription.portal'))->assertRedirect('https://bida-events.lemonsqueezy.com/billing');
    }

    public function test_a_signed_link_is_not_logged(): void
    {
        $user = $this->client();
        $this->portalReturns('https://bida-events.lemonsqueezy.com/billing?expires=1&user=1&signature=abc');

        Log::shouldReceive('warning')->never();

        $this->actingAs($user)->get(route('client.subscription.portal'))->assertRedirect();
    }

    public function test_the_plan_page_tells_what_to_do_if_lemon_squeezy_asks_to_log_in(): void
    {
        $user = $this->client();
        $this->configureLemonSqueezy();

        $this->actingAs($user)->withoutVite()->get(route('client.subscription'))
            ->assertOk()
            ->assertSee('usa el correo con el que pagaste');
    }
}