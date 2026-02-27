<div>
    <x-input-label for="captcha" value="Security check: what is {{ $question }} ?" />
    <x-text-input
        id="captcha"
        name="captcha"
        type="number"
        class="block mt-1 w-full"
        wire:model="{{ $attributes->get('wire:model', 'captcha') }}"
        autocomplete="off"
        inputmode="numeric"
        placeholder="Your answer"
        required
    />
    @if($errors->has($attributes->get('error-bag', 'captcha')))
        <x-input-error :messages="$errors->get($attributes->get('error-bag', 'captcha'))" class="mt-1" />
    @endif
</div>
