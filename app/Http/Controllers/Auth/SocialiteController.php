<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Socialite;
// use Laravel\Socialite\Facades\Socialite;
use Auth;
use App\Models\SocialAccount;
use App\User;

class SocialiteController extends Controller
{
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback($provider)
    {
        try {
            $user = Socialite::driver($provider)->user();
        } catch (Exception $th) {
            dd($th);
            return redirect('/login');
        }

        $authUser = $this->findOrCreateUser($user , $provider);

        Auth::login($authUser , true);
        
        return redirect('/home');
    }

    public function findOrCreateUser($socialUser , $provider)
    {
        $SocialAccount = SocialAccount::where('provider_id' , $socialUser->getId())
                        ->where('provider_name' , $provider)
                        ->first();
        if ($SocialAccount) {
            return $SocialAccount->user;
        } else {
            $user = User::where('email' , $socialUser->getEmail())->first();
            
            if (!$user) {
                $user = User::create([
                    'name'  => $socialUser->getName(),
                    'email' => $socialUser->getEmail(),
                ]);
            }

            $user->SocialAccount()->create([
                'provider_id'   => $socialUser->getId(),
                'provider_name' => $provider
            ]);

            return $user;
        }
    }
}
