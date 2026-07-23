<?php

namespace App\Http\Controllers;

use App\Mail\VerifyEmail;
use App\Models\Profile;
use App\Models\User;
use App\Services\Turnstile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SellerRegistrationController extends Controller
{
    public function regStepFirst()
    {
        return view('auth.seller.reg_step_first');
    }

    public function regStepSecond()
    {
        if (! auth()->check()) {
            return redirect(route('seller.registration'));
        }

        $user = auth()->user();

        if ((int) $user->role_id !== 3) {
            if ($user->email_verified_at === null) {
                return redirect()->route('verify.massage');
            }

            return redirect()->route('buyer.dashboard');
        }

        if ($user->verification) {
            return redirect()->route('seller.dashboard')->with('success', 'Du hast dich erfolgreich angemeldet.');
        }

        if ($user->email_verified_at !== null) {
            return view('auth.seller.reg_step_second');
        }

        return view('verify_massage', ['user' => $user]);
    }

    public function regStepFirstStore(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:40'],
            'last_name' => ['required', 'string', 'max:40'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'meta.vat' => [
                'required',
                'regex:/^(\d{11}|\d{13}|\d{2}\/\d{3}\/\d{5}|\d{3}\/\d{4}\/\d{4}|\d{3}\/\d{3}\/\d{5}|\d{5}\/\d{5})$/',
            ],
            'meta.is_pay_vat' => ['required', 'in:0,1'],
            'datenschutz' => ['required'],
            'vakuumierer' => ['required'],
            'nutzungsbedingungen' => ['required'],
        ], [
            'meta.vat.required' => 'Das Feld ist erforderlich',
            'meta.vat.regex' => 'Bitte gebe eine gültige Steuernummer an.',
        ]);

        if (env('CAPTCHA') == true) {
            $token = $request->input('cf-turnstile-response');
            $response = Turnstile::validateTurnstile($token);

            if (! $response->success) {
                return back()
                    ->withErrors(['cf-turnstile-response' => 'Bitte bestätige das CAPTCHA.'])
                    ->withInput();
            }
        }

        $user = User::create([
            'username' => $request->username,
            'name' => $request->name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => 3,
        ]);

        Profile::create([
            'user_id' => $user->id,
            'username' => $request->username,
        ]);

        $user->createMetas([
            'vat' => $request->input('meta.vat'),
            'is_pay_vat' => $request->input('meta.is_pay_vat'),
        ]);

        $token = Str::random(60);
        $user->update(['verifi_token' => $token]);

        try {
            Mail::to($user->email)->send(new VerifyEmail($user, $token));
        } catch (\Throwable $exception) {
            Log::warning('Verkäufer-Registrierungs-E-Mail konnte nicht gesendet werden.', [
                'user_id' => $user->id,
                'message' => $exception->getMessage(),
            ]);
        }

        Auth::login($user);

        return redirect()->route('seller.verification');
    }

    public function regStepSecondStore(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string'],
            'name' => ['required', 'max:40'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
        ]);
    }
}
