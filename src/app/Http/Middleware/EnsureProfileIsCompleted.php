<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureProfileIsCompleted
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user && empty($user->postcode)) {
            return redirect()->route('profile.edit');
        }

        return $next($request);
    }
}
