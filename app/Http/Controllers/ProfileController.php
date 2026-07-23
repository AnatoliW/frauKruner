<?php

namespace App\Http\Controllers;

use App\Mail\ShippedEmail;
use App\Models\Address;
use App\Models\Method;
use App\Models\Profile;
use App\Models\Verification;
use App\Order;
use App\Services\ExifMetadataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function test()
    {
        $order = Order::first();

        return new ShippedEmail($order);
    }

    public function info(Request $request)
    {
        $request->validate(
            [
                'username' => 'nullable|string',
                'profile_img' => 'nullable|image|max:10000',
                'description' => 'nullable|string',
                'meta.vat' => [
                    Rule::requiredIf(auth()->user()->role_id == 3),
                    'regex:/^(\d{11}|\d{13}|\d{2}\/\d{3}\/\d{5}|\d{3}\/\d{4}\/\d{4}|\d{3}\/\d{3}\/\d{5}|\d{5}\/\d{5})$/',
                ],
            ],
            [
                'meta.vat.required' => 'Das Feld ist erforderlich',
                'meta.vat.regex' => 'Bitte gebe eine gültige Steuernummer an.',
            ]
        );

        try {
            DB::beginTransaction();

            $profile = Profile::updateOrCreate(['user_id' => auth()->id()], [
                'username' => $request->username,
                'description' => $request->description,
            ]);

            if ($request->file('profile_img')) {
                if (filled($profile->profile_img) && Storage::exists($profile->profile_img)) {
                    Storage::delete($profile->profile_img);
                }
                $profile->update(['profile_img' => $request->profile_img->store('profile')]);
                $exifService = new ExifMetadataService();
                $processImage = $exifService->removeExifMetadata($profile->profile_img);
                if ($processImage) {
                    $profile->update([
                        'meta_remove_status' => 1,
                    ]);
                }
            }

            DB::commit();

            $user = auth()->user();
            if ($request->has('username')) {
                $user->update([
                    'username' => $request->username,
                ]);
            }
            if ($request->has('meta')) {
                $user->createMetas($request->meta);
            }

            return redirect()->back()->with('success', 'Benutzerdaten aktualisiert');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        } catch (\Error $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function address(Request $request)
    {
        // dd($request->all());
        $request->validate([
            // 'first_name' => 'required|string',
            // 'last_name' => 'required|string',
            // 'additional' => 'nullable|string',
            // 'street' => 'required|string',
            // 'house_no' => 'required|string',
            // 'zip' => 'required|string',
            // 'federal_state' => 'required|string',
            // 'po_box' => 'required|string',
            // 'vat_id' => 'nullable|string',
        ]);
        // try {
            DB::beginTransaction();
            Address::updateOrCreate(['user_id' => auth()->id()], [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'additional' => $request->additional,
                'street' => $request->street,
                'house_no' => $request->house_no,
                'zip' => $request->zip,
                'federal_state' => $request->federal_state,
                'po_box' => $request->po_box,
                'vat_id' => $request->vat_id,
                'paypal_email' => $request->paypal_email,
            ]);
            $user = auth()->user();
            $user->update([
                'name' => $request->first_name,
                'last_name' => $request->last_name,
            ]);

            if ($request->bic !== null || $request->iban !== null) {
                Method::updateOrCreate(
                    [
                        'user_id' => auth()->id(),
                    ],
                    [
                        'iban' => $request->iban,
                        'bic' => $request->bic,
                    ]
                );
            }
            if ($request->has('email')) {
                $user->update([
                    'email' => $request->email,
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Adresse aktualisiert');
        // } catch (\Exception $e) {
        //     return redirect()->back()->withErrors($e->getMessage());
        // } catch (\Error $e) {
        //     return redirect()->back()->withErrors($e->getMessage());
        // }
    }

    public function verification(Request $request)
    {
        $rules = [
            'street' => 'required|string',
            'house_no' => 'required|string',
            'city' => 'nullable|string',
            'zip' => 'required|string',
            'date_of_birth' => 'required|date',
            'iban' => 'nullable|string',
            'bic' => 'nullable|string',
        ];

        if ($request->update == '1') {
            $rules['person_id_shot_img'] = 'nullable|image';
            $rules['id_card_front_img'] = 'nullable|image';
            $rules['id_card_back_img'] = 'nullable|image';
        } else {
            $rules['person_id_shot_img'] = 'required|image';
            $rules['id_card_front_img'] = 'required|image';
            $rules['id_card_back_img'] = 'required|image';
            $rules['iban'] = 'required|string';
            $rules['bic'] = 'required|string';
        }

        $request->validate($rules);
        try {
            DB::beginTransaction();
            $verification = Verification::updateOrCreate(['user_id' => auth()->id()], [
                'street' => $request->street,
                'house_no' => $request->house_no,
                'city' => $request->city,
                'zip' => $request->zip,
                'date_of_birth' => $request->date_of_birth,
            ]);

            if ($request->files) {
                if ($request->person_id_shot_img) {
                    if (filled($verification->person_id_shot_img) && Storage::exists($verification->person_id_shot_img)) {
                        Storage::delete($verification->person_id_shot_img);
                    }
                    $verification->update([
                        'person_id_shot_img' => $request->person_id_shot_img->store('verifcation'),
                    ]);
                }
                if ($request->id_card_front_img) {
                    if (filled($verification->id_card_front_img) && Storage::exists($verification->id_card_front_img)) {
                        Storage::delete($verification->id_card_front_img);
                    }
                    $verification->update([
                        'id_card_front_img' => $request->id_card_front_img->store('verifcation'),
                    ]);
                }
                if ($request->id_card_back_img) {
                    if (filled($verification->id_card_back_img) && Storage::exists($verification->id_card_back_img)) {
                        Storage::delete($verification->id_card_back_img);
                    }
                    $verification->update([
                        'id_card_back_img' => $request->id_card_back_img->store('verifcation'),
                    ]);
                }
            }

            if ($request->update != '1') {
                Method::updateOrCreate(['user_id' => auth()->id()], [
                    'iban' => $request->iban,
                    'bic' => $request->bic,
                ]);
            } elseif (filled($request->iban) && filled($request->bic)) {
                Method::updateOrCreate(['user_id' => auth()->id()], [
                    'iban' => $request->iban,
                    'bic' => $request->bic,
                ]);
            }
            DB::commit();

            return redirect()->back()->with('success', 'Bestätigungsdetails aktualisiert');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        } catch (\Error $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }
}
