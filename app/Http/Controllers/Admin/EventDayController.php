<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\RsvpGuest;
use Illuminate\Http\Request;

class EventDayController extends Controller
{
    private function attendingGuests(?string $search = null)
    {
        return RsvpGuest::query()
            ->with('rsvp.invitation')
            ->whereHas('rsvp', fn ($r) => $r->where('attending', true)
                ->whereHas('invitation', fn ($i) => $i->whereNull('revoked_at')))
            ->when($search, fn ($q) => $q->where('name', 'like', "%$search%"))
            ->orderBy('name');
    }

    public function register()
    {
        return view('admin.register', ['guests' => $this->attendingGuests()->get()]);
    }

    public function checkin(Request $request)
    {
        return view('admin.checkin', [
            'guests' => $this->attendingGuests($request->query('q'))->get(),
        ]);
    }

    public function toggle(RsvpGuest $guest)
    {
        $guest->update(['checked_in_at' => $guest->checked_in_at ? null : now()]);

        return back();
    }

    public function missing()
    {
        return redirect()->route('admin.invitations.index', ['status' => 'pending']);
    }
}
