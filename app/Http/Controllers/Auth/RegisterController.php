<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\VerifyEmail;
use App\Models\User;
use App\Services\Turnstile;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    use RegistersUsers;

    public function __construct()
    {
        $this->middleware('guest');
    }

    public function redirectTo()
    {
        $user = auth()->user();

        if (! $user) {
            return route('home');
        }

        return match ((int) $user->role_id) {
            2 => route('buyer.dashboard'),
            3 => route('seller.verification'),
            default => route('home'),
        };
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:40'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'datenschutz' => ['required'],
        ]);
    }

    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id' => (int) ($data['role'] ?? 2),
        ]);
    }

    public function register(Request $request)
    {
        if (env('CAPTCHA') == true) {
            $token = $request->input('cf-turnstile-response');
            $response = Turnstile::validateTurnstile($token);

            if (! $response->success) {
                return back()
                    ->withErrors(['cf-turnstile-response' => 'Bitte bestätige das CAPTCHA.'])
                    ->withInput();
            }
        }

        $this->validator($request->all())->validate();

        $user = $this->create($request->all());

        $token = Str::random(60);
        $user->update(['verifi_token' => $token]);

        try {
            Mail::to($user->email)->send(new VerifyEmail($user, $token));
        } catch (\Throwable $exception) {
            Log::warning('Registrierungs-E-Mail konnte nicht gesendet werden.', [
                'user_id' => $user->id,
                'message' => $exception->getMessage(),
            ]);
        }

        Auth::login($user);

        if ((int) $user->role_id === 2) {
            return redirect()->route('verify.massage');
        }

        return redirect($this->redirectTo());
    }
}
