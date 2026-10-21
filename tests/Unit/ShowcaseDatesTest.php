<?php

namespace Tests\Unit;

use App\Support\ShowcaseDates;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class ShowcaseDatesTest extends TestCase
{
    private function sample(string $date = '2026-10-17 20:30:00', string $expires = '2027-04-17', ?string $season = null): array
    {
        return [
            'invitation' => [
                'event_date' => $date,
                'expires_at' => $expires,
                'event_type' => ['slug' => 'x', 'season' => $season],
            ],
            'modules' => [
                'bienvenida' => ['fecha_texto' => 'Sábado 17 de octubre', 'nombre' => 'Daniela'],
                'rsvp' => ['mensaje_personalizado' => 'Confirma antes del 10 de octubre para guardarte un lugar.'],
                'historia' => ['cuando' => 'Marzo 2019', 'texto' => 'Mayores — Becky G'],
            ],
            'guests' => [['confirmed_at' => '2026-09-09 01:17:01']],
        ];
    }

    public function test_a_past_date_moves_forward_whole_weeks_so_the_weekday_stays(): void
    {
        $data = ShowcaseDates::roll($this->sample(), CarbonImmutable::parse('2026-10-21 10:00'));

        // 17 de octubre + 7 semanas = 5 de diciembre: sábado, a 45 días de hoy
        $this->assertSame('2026-12-05 20:30:00', $data['invitation']['event_date']);
        $this->assertSame('Sábado 5 de diciembre', $data['modules']['bienvenida']['fecha_texto']);
        $this->assertSame('Confirma antes del 28 de noviembre para guardarte un lugar.', $data['modules']['rsvp']['mensaje_personalizado']);
        $this->assertSame(45, (int) CarbonImmutable::parse('2026-10-21')->diffInDays('2026-12-05'));
    }

    public function test_texts_without_a_date_and_guest_activity_stay_as_they_are(): void
    {
        $data = ShowcaseDates::roll($this->sample(), CarbonImmutable::parse('2026-10-21'));

        $this->assertSame('Daniela', $data['modules']['bienvenida']['nombre']);
        $this->assertSame('Marzo 2019', $data['modules']['historia']['cuando']);
        $this->assertSame('Mayores — Becky G', $data['modules']['historia']['texto']);
        $this->assertSame('2026-09-09 01:17:01', $data['guests'][0]['confirmed_at']);
    }

    public function test_a_date_that_is_far_enough_is_left_alone(): void
    {
        $data = ShowcaseDates::roll($this->sample('2026-12-12 16:00:00', '2027-06-12'), CarbonImmutable::parse('2026-10-21'));

        $this->assertSame('2026-12-12 16:00:00', $data['invitation']['event_date']);
        $this->assertSame('2027-06-12', $data['invitation']['expires_at']);
        $this->assertSame('Sábado 17 de octubre', $data['modules']['bienvenida']['fecha_texto']);
    }

    public function test_the_year_is_corrected_when_the_text_carries_it(): void
    {
        $data = $this->sample('2026-12-12 16:00:00', '2027-06-12');
        $data['modules']['bienvenida']['fecha_texto'] = 'Sábado 12 de diciembre, 2026';

        $rolled = ShowcaseDates::roll($data, CarbonImmutable::parse('2026-11-20'));

        $this->assertSame('2027-01-09 16:00:00', $rolled['invitation']['event_date']);
        $this->assertSame('Sábado 9 de enero, 2027', $rolled['modules']['bienvenida']['fecha_texto']);
    }

    public function test_the_expiry_keeps_its_distance_and_never_gets_close_to_today(): void
    {
        $data = ShowcaseDates::roll($this->sample(), CarbonImmutable::parse('2026-10-21'));

        // Vencía 182 días después del evento y sigue igual
        $this->assertSame('2027-06-05', $data['invitation']['expires_at']);

        $short = ShowcaseDates::roll($this->sample('2026-10-17 20:30:00', '2026-10-18'), CarbonImmutable::parse('2026-10-21'));
        $this->assertSame('2027-02-18', $short['invitation']['expires_at']);
    }

    public function test_seasonal_samples_keep_their_calendar_day_and_recalculate_the_weekday(): void
    {
        $halloween = $this->sample('2026-10-31 21:00:00', '2027-04-30', 'halloween');
        $halloween['modules']['bienvenida']['fecha_texto'] = 'Sábado 31 de octubre';
        $halloween['modules']['rsvp']['mensaje_personalizado'] = 'Confirma antes del 28 de octubre para contar los dulces.';

        // Todavía no pasó: se queda
        $thisYear = ShowcaseDates::roll($halloween, CarbonImmutable::parse('2026-10-21'));
        $this->assertSame('2026-10-31 21:00:00', $thisYear['invitation']['event_date']);
        $this->assertSame('Sábado 31 de octubre', $thisYear['modules']['bienvenida']['fecha_texto']);

        // Ya pasó: el año que viene, y 31 de octubre de 2027 cae domingo
        $nextYear = ShowcaseDates::roll($halloween, CarbonImmutable::parse('2026-11-02'));
        $this->assertSame('2027-10-31 21:00:00', $nextYear['invitation']['event_date']);
        $this->assertSame('Domingo 31 de octubre', $nextYear['modules']['bienvenida']['fecha_texto']);
        $this->assertSame('Confirma antes del 28 de octubre para contar los dulces.', $nextYear['modules']['rsvp']['mensaje_personalizado']);
        $this->assertSame('2028-04-29', $nextYear['invitation']['expires_at']);
    }

    public function test_rolling_twice_gives_the_same_result(): void
    {
        $now = CarbonImmutable::parse('2026-11-25');
        $once = ShowcaseDates::roll($this->sample(), $now);

        $this->assertSame($once, ShowcaseDates::roll($once, $now));
    }
}
