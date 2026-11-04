<?php

use App\Models\User;
use Illuminate\Support\Facades\Password;

test('ganti password memperbarui password_view', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->from('/')->put('/password', [
        'current_password' => 'password',
        'password' => 'sandi-baru-123',
        'password_confirmation' => 'sandi-baru-123',
    ]);
    expect($user->fresh()->password_view)->toBe('sandi-baru-123');
});

test('reset password memperbarui password_view', function () {
    $user = User::factory()->create();
    $token = Password::createToken($user);
    $this->post('/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'sandi-baru-123',
        'password_confirmation' => 'sandi-baru-123',
    ]);
    expect($user->fresh()->password_view)->toBe('sandi-baru-123');
});
