<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * ユーザーをTwitterの認証ページにリダイレクトする
     */
    public function redirectToProvider()
    {
        if(Auth::check()) {
            return redirect()->route('welcome');
        }

        return Socialite::driver('twitter')->redirect();
    }

    /**
     * Twitterからユーザー情報を取得する
     */
    public function handleProviderCallback()
    {
        if(Auth::check()) {
            redirect()->route('welcome');
        }

        try {
            $user = Socialite::driver('twitter')->user();
        } catch (\Exception $e) {
            return redirect('auth/twitter');
        }

        $authUser = $this->findOrCreateUser($user);
        Auth::login($authUser, true);

        return view('welcome');
    }

    public function logout()
    {
        Auth::logout();

        return redirect()->route('welcome');
    }

    private function findOrCreateUser($user)
    {
        $authUser = User::where('twitter_id', $user->id)->first();
        if ($authUser){
            $authUser->name = $user->name;
            $authUser->nickname = $user->nickname;
            $authUser->twitter_id = $user->id;
            $authUser->token = $user->token;
            $authUser->token_secret = $user->tokenSecret;
            $authUser->save();
            return $authUser;
        }
        return User::create([
            'name' => $user->name,
            'nickname' => $user->nickname,
            'twitter_id' => $user->id,
            'token' => $user->token,
            'token_secret' => $user->tokenSecret,
        ]);
    }
}
