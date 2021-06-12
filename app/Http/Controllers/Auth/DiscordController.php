<?php

namespace App\Http\Controllers\Auth;

use App\Discord;
use App\Models\Portal\User;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Auth\Events\Registered;
use Laravel\Socialite\Facades\Socialite;

class DiscordController extends Controller
{
    /**
     * Redirect the user to the Discord login page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirect()
    {
        return Socialite::driver('discord')->redirect();
    }

    /**
     * Handles the response from Discord.
     *
     * @return \Illuminate\Http\Response
     */
    public function callback(Request $request)
    {
        $user = Socialite::driver('discord')->user();

        if (Discord::isMember($user)) {
            return $this->create($user);
        }

        return [
            'error' => 'You are not a member.'
        ];
    }

    /**
     * Creates the user account and redirects with the access token.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($data)
    {
        $user = User::create([
            'discord_id' => $data->id,
            'name' => $data->name,
            'email' => $data->email,
            'avatar' => $data->avatar,
        ]);

        event(new Registered($user));
        auth()->login($user, true);

        return redirect('/hub');
    }
}
