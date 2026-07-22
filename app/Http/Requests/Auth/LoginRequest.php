<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Allow login by username (no email required)
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $credentials = $this->only('username', 'password');

        if (Auth::attempt($credentials, $this->boolean('remember'))) {
            RateLimiter::clear($this->throttleKey());
            return;
        }

        // Fallback: the application historically stored staff users in tbl_staff.
        // If a staff entry exists with matching username and password, create or
        // find a corresponding record in `users` and log that user in.
        $username = $this->string('username');
        $password = $this->string('password');

        $staff = Staff::where('username', $username)->first();
        if ($staff) {
            $staffPw = $staff->password;
            $matches = false;
            // staff password may be bcrypt or plain; check both safely
            if (! empty($staffPw)) {
                if (Hash::check($password, $staffPw)) {
                    $matches = true;
                } elseif (password_verify($password, $staffPw)) {
                    $matches = true;
                } elseif ($password === $staffPw) {
                    // last resort: plain text match (not recommended)
                    $matches = true;
                }
            }

            if ($matches) {
                // Ensure a corresponding users row exists so Laravel auth works consistently.
                $email = null;
                if (str_contains($username, '@')) {
                    $email = $username;
                } else {
                    // craft a deterministic local email to satisfy DB constraints
                    $email = $username . '@staff.local';
                    // ensure uniqueness by appending staffID if needed
                    if (User::where('email', $email)->exists()) {
                        $email = $username . '+' . $staff->staffID . '@staff.local';
                    }
                }

                $user = User::firstOrCreate(
                    ['username' => $username],
                    [
                        'name' => $staff->staffName ?? $username,
                        'email' => $email,
                        'password' => Hash::make($password),
                        'role' => $staff->role ?? 'mesero',
                    ]
                );

                Auth::loginUsingId($user->id, $this->boolean('remember'));
                RateLimiter::clear($this->throttleKey());
                return;
            }
        }

        RateLimiter::hit($this->throttleKey());

        throw ValidationException::withMessages([
            'username' => trans('auth.failed'),
        ]);

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('username')).'|'.$this->ip());
    }
}
