<?php

namespace Tests\Feature;

use App\Models\SubscriptionPayment;
use App\Models\User;
use App\Support\SiteSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * El administrador da de alta revendedores, registra sus pagos y ve a quién le toca renovar.
 */
class ResellerAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(Carbon::parse('2026-10-15 12:00:00', 'America/La_Paz'));
        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    public function test_the_admin_creates_a_reseller_with_dictable_credentials(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.resellers.store'), ['name' => 'Carla Mendoza', 'plan' => 'emprendedor', 'business_name' => 'Estudio Luz'])
            ->assertRedirect(route('admin.resellers.index'));

        $credentials = session('reseller_credentials');
        $reseller = User::where('username', 'carla.mendoza')->firstOrFail();

        $this->assertTrue($reseller->isReseller());
        $this->assertFalse($reseller->isAdmin());
        $this->assertSame('emprendedor', $reseller->reseller_plan);
        $this->assertSame('Estudio Luz', $reseller->business_name);
        // Sin pago todavía no está activo
        $this->assertFalse($reseller->hasActiveSubscription());
        $this->assertMatchesRegularExpression('/^[a-z]{3,12}-[a-z]{3,12}-[1-9][0-9]{2}$/', $credentials['password']);
        $this->assertTrue(Hash::check($credentials['password'], $reseller->password));

        $response->assertSessionHas('success');
    }

    public function test_an_unknown_plan_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.resellers.store'), ['name' => 'Carla', 'plan' => 'gratis'])
            ->assertSessionHasErrors('plan');

        $this->assertDatabaseMissing('users', ['name' => 'Carla']);
    }

    public function test_a_payment_activates_the_subscription_and_extends_it(): void
    {
        $reseller = User::factory()->create(['is_reseller' => true, 'reseller_plan' => 'aliado']);

        $this->actingAs($this->admin)
            ->post(route('admin.resellers.payments.store', $reseller), ['plan' => 'aliado', 'amount' => 120, 'note' => 'Transferencia', 'request_token' => (string) Str::uuid()])
            ->assertRedirect()
            ->assertSessionHas('success');

        $reseller->refresh();
        $this->assertTrue($reseller->hasActiveSubscription());
        $this->assertSame('2026-11-15', $reseller->subscription_renews_at->toDateString());

        $payment = SubscriptionPayment::firstOrFail();
        $this->assertSame($this->admin->id, $payment->registered_by);
        $this->assertSame('120.00', $payment->amount);
        $this->assertSame('2026-11-15', $payment->renews_until->toDateString());

        // Un segundo pago antes de vencer suma otro mes y puede cambiar de plan
        $this->actingAs($this->admin)
            ->post(route('admin.resellers.payments.store', $reseller), ['plan' => 'agencia', 'amount' => 500, 'request_token' => (string) Str::uuid()]);

        $reseller->refresh();
        $this->assertSame('2026-12-15', $reseller->subscription_renews_at->toDateString());
        $this->assertSame('agencia', $reseller->reseller_plan);
        $this->assertCount(2, $reseller->subscriptionPayments);
    }

    /** Lo que pasó en producción: el mismo formulario enviado dos veces sumaba dos meses. */
    public function test_the_same_payment_form_sent_twice_counts_once(): void
    {
        $reseller = User::factory()->create(['is_reseller' => true, 'reseller_plan' => 'emprendedor']);
        $payload = ['plan' => 'emprendedor', 'amount' => 250, 'request_token' => (string) Str::uuid()];

        $this->actingAs($this->admin)->post(route('admin.resellers.payments.store', $reseller), $payload);
        $this->actingAs($this->admin)
            ->post(route('admin.resellers.payments.store', $reseller), $payload)
            ->assertSessionHas('success', 'Ese pago ya estaba registrado: no se sumó otro mes.');

        $this->assertDatabaseCount('subscription_payments', 1);
        $this->assertSame('2026-11-15', $reseller->fresh()->subscription_renews_at->toDateString());

        // Sin código del formulario no se registra
        $this->actingAs($this->admin)
            ->post(route('admin.resellers.payments.store', $reseller), ['plan' => 'emprendedor', 'amount' => 250])
            ->assertSessionHasErrors('request_token');
    }

    public function test_the_last_payment_can_be_annulled_and_the_date_goes_back(): void
    {
        $reseller = User::factory()->create(['is_reseller' => true, 'reseller_plan' => 'aliado']);

        foreach (range(1, 2) as $ignored) {
            $this->actingAs($this->admin)->post(route('admin.resellers.payments.store', $reseller), [
                'plan' => 'aliado', 'amount' => 120, 'request_token' => (string) Str::uuid(),
            ]);
        }

        [$first, $second] = SubscriptionPayment::orderBy('id')->get()->all();
        $this->assertSame('2026-12-15', $reseller->fresh()->subscription_renews_at->toDateString());

        // Solo el último se anula
        $this->actingAs($this->admin)
            ->delete(route('admin.resellers.payments.destroy', [$reseller, $first]))
            ->assertStatus(422);

        $this->actingAs($this->admin)
            ->delete(route('admin.resellers.payments.destroy', [$reseller, $second]))
            ->assertRedirect();

        $this->assertModelMissing($second);
        $this->assertSame('2026-11-15', $reseller->fresh()->subscription_renews_at->toDateString());

        // Anulado también el primero, la suscripción queda sin activar
        $this->actingAs($this->admin)->delete(route('admin.resellers.payments.destroy', [$reseller, $first]));
        $this->assertNull($reseller->fresh()->subscription_renews_at);
        $this->assertFalse($reseller->fresh()->hasActiveSubscription());
    }

    public function test_the_admin_sets_the_price_and_quota_of_each_plan(): void
    {
        $this->actingAs($this->admin)->put(route('admin.settings.update'), [
            'promo_active' => '1',
            'packages' => collect(config('bida.packages'))->mapWithKeys(fn ($p) => [$p['key'] => ['price' => $p['price'], 'promo_price' => $p['promo_price'] ?? '']])->all(),
            'reseller_plans' => [
                'aliado' => ['price' => 140, 'quota_per_month' => 12],
                'agencia' => ['price' => 600, 'quota_per_month' => ''],
            ],
        ])->assertSessionHasNoErrors();

        SiteSettings::apply();

        $this->assertSame(140, config('bida.reseller_plans.aliado.price'));
        $this->assertSame(12, config('bida.reseller_plans.aliado.quota_per_month'));
        $this->assertSame(600, config('bida.reseller_plans.agencia.price'));
        $this->assertNull(config('bida.reseller_plans.agencia.quota_per_month'));

        SiteSettings::forget();
    }

    public function test_payments_are_only_for_resellers(): void
    {
        $client = User::factory()->create();

        $this->actingAs($this->admin)
            ->post(route('admin.resellers.payments.store', $client), ['plan' => 'aliado', 'amount' => 120, 'request_token' => (string) Str::uuid()])
            ->assertNotFound();

        $this->assertDatabaseCount('subscription_payments', 0);
    }

    public function test_the_list_starts_with_who_expires_first_and_highlights_them(): void
    {
        User::factory()->reseller(renewsAt: '2026-12-01')->create(['name' => 'Tranquila al día']);
        User::factory()->reseller(renewsAt: '2026-10-18')->create(['name' => 'Vence en tres días']);
        User::factory()->reseller(renewsAt: '2026-10-10')->create(['name' => 'Ya vencida']);
        User::factory()->create(['is_reseller' => true, 'reseller_plan' => 'aliado', 'name' => 'Nunca pagó']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.resellers.index'))
            ->assertOk()
            // Aviso de vencimientos arriba, con el enlace para escribirles
            ->assertSeeInOrder(['Vencen en los próximos 5 días', 'Ya vencida', 'Vence en tres días', 'Revendedor'])
            ->assertSee('https://wa.me/?text=', false)
            // Tabla: primero el que vence antes, al final quien nunca pagó
            ->assertSeeInOrder(['Revendedor', 'Ya vencida', 'Vence en tres días', 'Tranquila al día', 'Nunca pagó']);

        $this->assertStringNotContainsString('Tranquila al día</span>', explode('Revendedor', $response->getContent())[0]);
    }

    public function test_the_admin_navigation_counts_who_needs_a_reminder(): void
    {
        User::factory()->reseller(renewsAt: '2026-12-01')->create();
        User::factory()->reseller(renewsAt: '2026-10-18')->create();
        User::factory()->reseller(renewsAt: '2026-10-01')->create();

        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(route('admin.resellers.index'), false)
            ->assertSee('title="2 por vencer o vencidos"', false);
    }

    public function test_only_the_admin_manages_resellers(): void
    {
        $reseller = User::factory()->reseller()->create();

        $this->actingAs($reseller)->get(route('admin.resellers.index'))->assertForbidden();
        $this->actingAs($reseller)
            ->post(route('admin.resellers.payments.store', $reseller), ['plan' => 'agencia', 'amount' => 0, 'request_token' => (string) Str::uuid()])
            ->assertForbidden();
    }
}
