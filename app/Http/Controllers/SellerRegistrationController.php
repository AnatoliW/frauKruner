<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SellerRegistrationController extends Controller
{
    public function regStepFirst()
    {
        return view('auth.seller.reg_step_first');
    }
    public function regStepSecond()
    {

        if(!auth()->check()){
            return redirect(route('seller.registration'));
        }
        if (auth()->user()->verification) return redirect()->route('seller.dashboard')->with('success', 'Du hast dich erfolgreich angemeldet.');
        if(auth()->user()->email_verified_at==!null){
            return view('auth.seller.reg_step_second');
        }else{
            return view('auth.register');
        }

    }
    public function regStepFirstStore(Request $request)
    {
        // @dd($request->all);

        $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'name' => ['required', 'max:40'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],

        ]);
        User::create([
            'name' => $request->username,
            'last_name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => 3,
        ]);
        return redirect()->route('seller.registration.second')->with('success_msg', 'Seller create success');
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
