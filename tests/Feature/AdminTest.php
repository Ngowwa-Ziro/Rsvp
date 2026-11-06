<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\Rsvp;
use App\Models\Setting;
use App\Models\User;
use App\Services\QrService;
use App\Services\StatsService;
use App\Support\Phone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create();
    }

    public function test_guests_cannot_reach_admin_pages(): void
    {
        foreach (['/admin', '/admin/invitations', '/admin/export', '/admin/settings', '/admin/qr', '/admin/register'] as $url) {
            $this->get($url)->assertRedirect('/admin/login');
        }
    }

    public function test_admin_can_log_in_and_see_dashboard(): void
    {
        $u = User::factory()->create(['password' => bcrypt('correct-horse-battery')]);
        $this->post('/admin/login', ['email' => $u->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->post('/admin/login', ['email' => $u->email, 'password' => 'correct-horse-battery'])->assertRedirect('/admin');
        $this->get('/admin')->assertOk()->assertSee('Dashboard');
    }

    public function test_attendance_counts_people_not_forms(): void
    {
        $this->seedRsvp(3);
        $this->seedRsvp(2);
        $this->seedRsvp(0, false);
        Invitation::factory()->create(); // pending

        $s = app(StatsService::class)->summary();
        $this->assertSame(5, $s['expected']);
        $this->assertSame(2, $s['confirmed']);
        $this->assertSame(1, $s['declined']);
        $this->assertSame(1, $s['pending']);
        $this->assertSame(4, $s['issued']);
        $this->assertSame(3, $s['additional_guests']); // 5 people - 2 invitees
        $this->assertSame(2, $s['parties_with_extras']);
        $this->assertSame(24, $s['allocated']);        // 4 x 6
        $this->assertSame(19, $s['remaining_allocated']);
        $this->assertEquals(75, $s['response_rate']);
    }

    public function test_creating_invitations_applies_category_allowance_and_unique_codes(): void
    {
        $this->actingAs($this->admin());
        $this->post('/admin/invitations', ['name' => 'Wanjiku', 'phone' => '0712 345 678', 'category' => 'friend'])->assertRedirect();
        $this->post('/admin/invitations', ['name' => 'The Otieno Family', 'phone' => '0722345678', 'category' => 'family'])->assertRedirect();

        $friend = Invitation::where('name', 'Wanjiku')->first();
        $family = Invitation::where('name', 'The Otieno Family')->first();
        $this->assertSame(2, $friend->max_attendees);
        $this->assertSame(6, $family->max_attendees);
        $this->assertSame('+254712345678', $friend->phone);
        $this->assertNotSame($friend->code, $family->code);
        $this->assertMatchesRegularExpression('/^[A-HJKMNP-Z2-9]{6}$/', $friend->code);
    }

    public function test_phone_validation_and_duplicate_invitations(): void
    {
        $this->actingAs($this->admin());
        $this->post('/admin/invitations', ['name' => 'X', 'phone' => '12345', 'category' => 'friend'])->assertSessionHasErrors('phone');
        $this->post('/admin/invitations', ['name' => 'A', 'phone' => '0712345678', 'category' => 'friend']);
        $this->post('/admin/invitations', ['name' => 'B', 'phone' => '+254712345678', 'category' => 'friend'])->assertSessionHasErrors('phone');
        $this->assertSame(1, Invitation::count());
    }

    public function test_phone_normalizer(): void
    {
        $this->assertSame('+254712345678', Phone::normalize('0712 345 678'));
        $this->assertSame('+254112345678', Phone::normalize('254112345678'));
        $this->assertNull(Phone::normalize('0812345678'));
        $this->assertNull(Phone::normalize('abc'));
    }

    public function test_admin_can_revoke_and_change_rsvp_on_behalf_after_deadline(): void
    {
        $this->actingAs($this->admin());
        $i = Invitation::factory()->create();
        $this->travelTo(now()->setDate(2026, 12, 1)); // after deadline
        $this->put("/admin/invitations/{$i->id}/rsvp", ['attending' => 1, 'guests' => ['A', 'B']])->assertRedirect();
        $this->assertSame(2, $i->fresh()->rsvp->guest_count);

        $this->delete("/admin/invitations/{$i->id}")->assertRedirect();
        $this->assertNotNull($i->fresh()->revoked_at);
    }

    public function test_csv_export_contains_data_and_is_admin_only(): void
    {
        $this->get('/admin/export')->assertRedirect('/admin/login');

        $this->actingAs($this->admin());
        $i = Invitation::factory()->create(['name' => 'Export Me', 'code' => 'EXP234']);
        $this->seedRsvp(2);
        $csv = $this->get('/admin/export')->assertOk()->streamedContent();
        $this->assertStringContainsString('Export Me', $csv);
        $this->assertStringContainsString('EXP234', $csv);
        $this->assertStringContainsString('people_attending', $csv);
    }

    public function test_csv_import_creates_invitations_and_reports_bad_rows(): void
    {
        $this->actingAs($this->admin());
        $csv = "name,phone,email,category,max_attendees\n"
            ."Grace,0711000001,,friend,\n"
            ."Mwangi Family,0711000002,m@example.com,family,\n"
            ."Bad Phone,123,,friend,\n"
            ."Grace Again,0711000001,,friend,\n";
        $file = UploadedFile::fake()->createWithContent('guests.csv', $csv);

        $this->post('/admin/import', ['file' => $file])->assertRedirect('/admin/import')
            ->assertSessionHas('import_errors', fn ($e) => count($e) === 2);

        $this->assertSame(2, Invitation::count());
        $this->assertSame(2, Invitation::where('name', 'Grace')->first()->max_attendees);
        $this->assertSame(6, Invitation::where('name', 'Mwangi Family')->first()->max_attendees);
    }

    public function test_qr_points_to_permanent_url_and_refuses_temporary_ones(): void
    {
        Setting::set('public_url', 'https://rsvp.example.org');
        $this->assertSame('https://rsvp.example.org/rsvp', app(QrService::class)->targetUrl());

        $this->actingAs($this->admin());
        $this->get('/admin/qr.svg')->assertOk()->assertHeader('Content-Type', 'image/svg+xml');

        foreach (['http://rsvp.example.org', 'https://localhost', 'https://abc.ngrok.io', 'https://192.168.1.5'] as $bad) {
            Setting::set('public_url', $bad);
            try {
                app(QrService::class)->targetUrl();
                $this->fail("QR should refuse $bad");
            } catch (\RuntimeException $e) {
                $this->assertNotEmpty($e->getMessage());
            }
        }
    }

    public function test_checkin_toggles(): void
    {
        $this->actingAs($this->admin());
        $this->seedRsvp(1);
        $guest = \App\Models\RsvpGuest::first();
        $this->patch("/admin/check-in/{$guest->id}")->assertRedirect();
        $this->assertNotNull($guest->fresh()->checked_in_at);
        $this->patch("/admin/check-in/{$guest->id}");
        $this->assertNull($guest->fresh()->checked_in_at);
    }

    private function seedRsvp(int $people, bool $attending = true): Invitation
    {
        $i = Invitation::factory()->create(['status' => $attending ? 'attending' : 'declined', 'responded_at' => now()]);
        $r = Rsvp::create(['invitation_id' => $i->id, 'attending' => $attending, 'guest_count' => $people]);
        for ($n = 1; $n <= $people; $n++) {
            $r->guests()->create(['name' => "Guest $n"]);
        }

        return $i;
    }
}
