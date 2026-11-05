<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\Rsvp;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RsvpFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-11-10 10:00', 'Africa/Nairobi'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function as(Invitation $i)
    {
        return $this->withSession(['rsvp.invitation_id' => $i->id]);
    }

    public function test_landing_page_loads(): void
    {
        $this->get('/rsvp')->assertOk()->assertSee('Joseph Ziro Ngowwa');
    }

    public function test_valid_code_proceeds_to_form(): void
    {
        $i = Invitation::factory()->create(['code' => 'ABC234']);
        $this->post('/rsvp/lookup', ['code' => 'abc 234'])->assertRedirect('/rsvp/respond');
        $this->as($i)->get('/rsvp/respond')->assertOk()->assertSee($i->name);
    }

    public function test_invalid_code_is_rejected(): void
    {
        $this->from('/rsvp')->post('/rsvp/lookup', ['code' => 'NOPE99'])->assertSessionHasErrors('code');
    }

    public function test_revoked_code_is_rejected(): void
    {
        Invitation::factory()->create(['code' => 'GONE22', 'revoked_at' => now()]);
        $this->post('/rsvp/lookup', ['code' => 'GONE22'])->assertSessionHasErrors('code');
    }

    public function test_family_can_confirm_with_multiple_attendees(): void
    {
        $i = Invitation::factory()->create();
        $this->as($i)->post('/rsvp/respond', [
            'attending' => 1, 'guests' => ['Amina', 'Baraka', 'Chao', '', ''], 'dietary' => 'Vegetarian',
        ])->assertRedirect('/rsvp/confirmation');

        $i->refresh();
        $this->assertSame('attending', $i->status);
        $this->assertSame(3, $i->rsvp->guest_count);
        $this->assertSame(3, $i->rsvp->guests()->count());
    }

    public function test_decline_is_recorded_without_guests(): void
    {
        $i = Invitation::factory()->create();
        $this->as($i)->post('/rsvp/respond', ['attending' => 0])->assertRedirect('/rsvp/confirmation');

        $i->refresh();
        $this->assertSame('declined', $i->status);
        $this->assertSame(0, $i->rsvp->guest_count);
        $this->assertSame(0, $i->rsvp->guests()->count());
    }

    public function test_friend_cannot_exceed_allowance_of_two(): void
    {
        $i = Invitation::factory()->friend()->create();
        $this->as($i)->post('/rsvp/respond', ['attending' => 1, 'guests' => ['A', 'B', 'C']])
            ->assertSessionHasErrors('guests');
        $this->assertDatabaseCount('rsvps', 0);
    }

    public function test_family_cannot_exceed_allowance_of_six(): void
    {
        $i = Invitation::factory()->create();
        $this->as($i)->post('/rsvp/respond', ['attending' => 1, 'guests' => ['1', '2', '3', '4', '5', '6', '7']])
            ->assertSessionHasErrors('guests');
    }

    public function test_resubmitting_updates_instead_of_duplicating(): void
    {
        $i = Invitation::factory()->create();
        $this->as($i)->post('/rsvp/respond', ['attending' => 1, 'guests' => ['A', 'B']]);
        $this->as($i)->post('/rsvp/respond', ['attending' => 1, 'guests' => ['A', 'B', 'C']]);
        $this->as($i)->post('/rsvp/respond', ['attending' => 0]);

        $this->assertSame(1, Rsvp::count());
        $this->assertSame(0, \App\Models\RsvpGuest::count());
        $this->assertSame('declined', $i->fresh()->status);
    }

    public function test_deadline_is_enforced_in_nairobi_time(): void
    {
        $i = Invitation::factory()->create(['code' => 'LATE22']);

        Carbon::setTestNow(Carbon::parse('2026-11-25 23:30', 'Africa/Nairobi')); // still open
        $this->as($i)->post('/rsvp/respond', ['attending' => 1, 'guests' => ['A']])->assertRedirect('/rsvp/confirmation');

        Carbon::setTestNow(Carbon::parse('2026-11-26 00:05', 'Africa/Nairobi')); // closed
        $this->as($i)->post('/rsvp/respond', ['attending' => 0])->assertSessionHasErrors('attending');
        $this->post('/rsvp/lookup', ['code' => 'LATE22'])->assertSessionHasErrors('code');
    }

    public function test_capacity_is_enforced(): void
    {
        Setting::set('capacity', '3');
        $a = Invitation::factory()->create();
        $b = Invitation::factory()->create();
        $this->as($a)->post('/rsvp/respond', ['attending' => 1, 'guests' => ['A', 'B']]);
        $this->as($b)->post('/rsvp/respond', ['attending' => 1, 'guests' => ['C', 'D']])->assertSessionHasErrors('guests');
    }

    public function test_honeypot_blocks_bots(): void
    {
        $i = Invitation::factory()->create();
        $this->as($i)->post('/rsvp/respond', ['attending' => 0, 'website' => 'http://spam.example'])
            ->assertSessionHasErrors('website');
        $this->assertDatabaseCount('rsvps', 0);
    }

    public function test_form_requires_a_valid_invitation_session(): void
    {
        $this->get('/rsvp/respond')->assertRedirect('/rsvp');
        $this->post('/rsvp/respond', ['attending' => 0])->assertRedirect('/rsvp');
    }
}
