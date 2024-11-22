<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Socialite;
use Auth;
use App\Models\UserTool;

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

            $existingTool = UserTool::where('email', $user->getEmail())
                ->where('tool_name', $provider)
                ->first();

            if ($existingTool) {
                Auth::login($existingTool->user);
            } else {
                $newTool = new UserTool();
                $newTool->user_id = Auth::id();
                $newTool->tool_name = $provider;
                $newTool->email = $user->getEmail();
                $newTool->display_name = $user->getNickname() ?: $user->getName();
                $newTool->save();
            }

            return redirect()->route('tasks.index');
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Failed to login using ' . $provider);
        }
    }
}