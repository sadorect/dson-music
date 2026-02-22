@php
    /**
     * Math CAPTCHA component.
     * Generates (or retrieves) a session-backed arithmetic question and renders
     * an input for the answer, plus an AJAX refresh button.
     */
    $captchaQuestion = \App\Services\CaptchaService::getQuestion();
@endphp

<div class="mt-4" id="captcha-wrapper">
    <x-input-label for="captcha_answer">
        <span class="flex items-center gap-1">
            🔐 Security Check
        </span>
    </x-input-label>

    <!-- Question display -->
    <div class="flex items-center gap-2 mt-1 mb-2">
        <p id="captcha-question"
           class="flex-1 text-sm font-semibold text-black bg-orange-50 border border-orange-200 rounded-md px-3 py-2 font-mono select-none">
            {{ $captchaQuestion }}
        </p>
        <button type="button"
                id="captcha-refresh-btn"
                onclick="refreshMathCaptcha()"
                title="Get a new question"
                class="flex items-center gap-1 px-3 py-2 text-xs font-medium bg-orange-100 text-orange-700 border border-orange-300 rounded-md hover:bg-orange-200 active:scale-95 transition-all whitespace-nowrap">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h5M20 20v-5h-5M4 9a9 9 0 0114.13-3.87L20 9M4 15l1.87 4.87A9 9 0 0020 15"/>
            </svg>
            New
        </button>
    </div>

    <!-- Answer input -->
    <x-text-input
        id="captcha_answer"
        name="captcha_answer"
        type="number"
        class="block w-full"
        :value="old('captcha_answer')"
        required
        autocomplete="off"
        inputmode="numeric"
        placeholder="Your answer…"
        aria-label="Captcha answer"
    />

    <x-input-error :messages="$errors->get('captcha_answer')" class="mt-2" />
</div>

<script>
function refreshMathCaptcha() {
    const btn  = document.getElementById('captcha-refresh-btn');
    const display = document.getElementById('captcha-question');
    const input   = document.getElementById('captcha_answer');

    btn.disabled = true;
    btn.innerHTML = '<svg class="w-3.5 h-3.5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>';

    fetch('{{ route('captcha.refresh') }}', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? ''
        }
    })
    .then(r => r.json())
    .then(data => {
        display.textContent = data.question;
        if (input) input.value = '';
    })
    .catch(() => window.location.reload())
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h5M20 20v-5h-5M4 9a9 9 0 0114.13-3.87L20 9M4 15l1.87 4.87A9 9 0 0020 15"/></svg> New`;
    });
}
</script>
