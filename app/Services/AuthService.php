<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthService
{
    public function attemptLogin(string $email, string $password, ?string $deviceName = null): string
    {
        $user = User::where('email', $email)->first();
        if ($user === null || Hash::check($password, $user->password) === false) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return $user->createToken($deviceName ?? 'api')->plainTextToken;
    }

    public function logout(User $user): void
    {
        /** @var PersonalAccessToken|null $token */
        $token = $user->currentAccessToken();
        if ($token !== null) {
            $token->delete();
        }
    }
}


