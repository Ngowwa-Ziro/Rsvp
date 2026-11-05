{{-- Shared by the public RSVP form and the admin "change on behalf" form.
     Vars: $invitation, $rsvp (nullable), $dark (bool: styled for the dark public page) --}}
@php
    $dark = $dark ?? false;
    $names = old('guests', $rsvp?->guests?->pluck('name')->all() ?: [$invitation->name]);
    $attending = old('attending', $rsvp ? (int) $rsvp->attending : null);
    $count = max(count(array_filter($names)), 1);
    $input = $dark
        ? 'w-full rounded-lg bg-forest-light border border-ivory/30 px-4 py-3 text-ivory placeholder-ivory/50 focus:border-copper'
        : 'w-full rounded-lg bg-white border border-charcoal/30 px-4 py-3';
    $label = $dark ? 'text-ivory' : 'text-charcoal';
@endphp

<fieldset class="mb-8">
    <legend class="font-serif text-2xl mb-3 {{ $label }}">Will you be attending?</legend>
    <div class="grid grid-cols-2 gap-3">
        @foreach ([1 => 'Joyfully accept', 0 => 'Regretfully decline'] as $val => $text)
            <label class="cursor-pointer">
                <input type="radio" name="attending" value="{{ $val }}" class="peer sr-only" @checked((string) $attending === (string) $val) required>
                <span class="block text-center rounded-lg border-2 px-4 py-4 font-medium transition
                    {{ $dark ? 'border-ivory/30 peer-checked:bg-ivory peer-checked:text-forest peer-checked:border-ivory' : 'border-charcoal/30 peer-checked:bg-forest peer-checked:text-ivory' }}
                    peer-focus-visible:ring-4 peer-focus-visible:ring-copper">{{ $text }}</span>
            </label>
        @endforeach
    </div>
</fieldset>

<div id="attending-fields" class="space-y-6">
    <div>
        <label for="party" class="block font-medium mb-2 {{ $label }}">
            How many people are coming, including you?
            <span class="opacity-70 text-sm">(up to {{ $invitation->max_attendees }})</span>
        </label>
        <select id="party" class="{{ $input }}">
            @for ($i = 1; $i <= $invitation->max_attendees; $i++)
                <option value="{{ $i }}" @selected($i === $count)>{{ $i }}</option>
            @endfor
        </select>
    </div>

    <div id="names" class="space-y-3">
        @for ($i = 0; $i < $invitation->max_attendees; $i++)
            <div data-row>
                <label class="block text-sm mb-1 {{ $label }}" for="guest-{{ $i }}">{{ $i === 0 ? 'Your name' : 'Guest '.($i + 1).' name' }}</label>
                <input id="guest-{{ $i }}" type="text" name="guests[]" maxlength="100" value="{{ $names[$i] ?? '' }}" class="{{ $input }}" autocomplete="off">
            </div>
        @endfor
    </div>

    <div>
        <label for="dietary" class="block font-medium mb-2 {{ $label }}">Dietary requirements <span class="opacity-70 text-sm">(optional)</span></label>
        <input id="dietary" type="text" name="dietary" maxlength="255" value="{{ old('dietary', $rsvp?->dietary) }}" class="{{ $input }}">
    </div>
</div>

<div class="mt-6">
    <label for="message" class="block font-medium mb-2 {{ $label }}">A message for Joseph <span class="opacity-70 text-sm">(optional)</span></label>
    <textarea id="message" name="message" rows="3" maxlength="1000" class="{{ $input }}">{{ old('message', $rsvp?->message) }}</textarea>
</div>

<div class="hidden" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

<script>
(function () {
    const radios = document.querySelectorAll('input[name=attending]');
    const box = document.getElementById('attending-fields');
    const party = document.getElementById('party');
    const rows = document.querySelectorAll('#names [data-row]');
    function sync() {
        const yes = document.querySelector('input[name=attending]:checked')?.value === '1';
        box.style.display = yes ? '' : 'none';
        rows.forEach((r, i) => { r.style.display = i < parseInt(party.value, 10) ? '' : 'none'; });
        if (!yes) rows.forEach(r => r.querySelector('input').value = '');
    }
    radios.forEach(r => r.addEventListener('change', sync));
    party.addEventListener('change', sync);
    sync();
})();
</script>
