<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public function login(): void
    {
        $credentials = $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $this->remember)) {
            $this->addError('email', 'Email atau password salah.');

            return;
        }

        request()->session()->regenerate();

        $this->redirectRoute('dashboard');
    }
};
?>

<div>
    <form wire:submit="login">
        <input type="email" wire:model="email" autocomplete="email">

        <input type="password" wire:model="password" autocomplete="current-password">

        <label>
            <input type="checkbox" wire:model="remember">
            Remember me
        </label>

        @error('email')
            <span>{{ $message }}</span>
        @enderror

        @error('password')
            <span>{{ $message }}</span>
        @enderror

        <button type="submit">
            Login
        </button>
    </form>
</div>