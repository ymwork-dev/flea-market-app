<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RestrictDemoAccount
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        abort_if($user && $user->isDemoAccount(), 403, 'デモアカウントのプロフィールは変更できません。');

        return $next($request);
    }
}
