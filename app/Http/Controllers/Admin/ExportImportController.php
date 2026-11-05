<?php

namespace App\Http\Controllers\Admin;

use App\Enums\GuestCategory;
use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Services\InvitationCodeGenerator;
use App\Support\Phone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ExportImportController extends Controller
{
    public function export()
    {
        $rows = Invitation::with('rsvp.guests')->orderBy('name')->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel opens it correctly
            fputcsv($out, ['code', 'name', 'phone', 'email', 'category', 'max_attendees', 'status', 'revoked', 'people_attending', 'guest_names', 'dietary', 'message', 'responded_at', 'checked_in']);
            foreach ($rows as $i) {
                $g = $i->rsvp?->guests ?? collect();
                fputcsv($out, [
                    $i->code, $i->name, $i->phone, $i->email, $i->category->value, $i->max_attendees, $i->status,
                    $i->revoked_at ? 'yes' : 'no', $i->rsvp?->attending ? $i->rsvp->guest_count : 0,
                    $g->pluck('name')->implode('; '), $i->rsvp?->dietary, $i->rsvp?->message,
                    $i->responded_at?->timezone('Africa/Nairobi')->format('Y-m-d H:i'),
                    $g->whereNotNull('checked_in_at')->count(),
                ]);
            }
            fclose($out);
        }, 'guest-list-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function importForm()
    {
        return view('admin.import');
    }

    public function import(Request $request, InvitationCodeGenerator $codes)
    {
        $request->validate(['file' => ['required', 'file', 'mimes:csv,txt', 'max:1024']]);

        $handle = fopen($request->file('file')->getRealPath(), 'r');
        $header = array_map(fn ($h) => strtolower(trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $h))), fgetcsv($handle) ?: []);
        foreach (['name', 'phone', 'category'] as $col) {
            if (! in_array($col, $header, true)) {
                return back()->withErrors(['file' => "Missing required column: $col (expected: name, phone, email, category, max_attendees)"]);
            }
        }

        $created = 0;
        $errors = [];
        $line = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $line++;
            if (count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }
            $data = array_combine($header, array_pad(array_slice($row, 0, count($header)), count($header), null));
            $data['phone'] = Phone::normalize($data['phone'] ?? '') ?? ($data['phone'] ?? '');
            $data['category'] = strtolower(trim((string) ($data['category'] ?? '')));
            $data['max_attendees'] = ($data['max_attendees'] ?? '') === '' ? null : $data['max_attendees'];
            $data['email'] = ($data['email'] ?? '') === '' ? null : $data['email'];

            $v = Validator::make($data, [
                'name' => ['required', 'string', 'max:120'],
                'phone' => ['required', 'regex:/^\+254[17]\d{8}$/', Rule::unique('invitations', 'phone')],
                'email' => ['nullable', 'email'],
                'category' => ['required', Rule::enum(GuestCategory::class)],
                'max_attendees' => ['nullable', 'integer', 'min:1', 'max:20'],
            ]);
            if ($v->fails()) {
                $errors[] = "Line $line: ".$v->errors()->first();
                continue;
            }
            Invitation::create($v->validated() + ['code' => $codes->generate()]);
            $created++;
        }
        fclose($handle);

        return redirect()->route('admin.import')->with('status', "Imported $created invitation(s).")->with('import_errors', $errors);
    }
}
