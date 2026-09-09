<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DemoLoginController extends Controller
{
    private const DEMO_EMAILS_BY_TYPE = [
        'seller' => 'test@example.com',
        'buyer' => 'demo@example.com',
    ];

    public function login(string $type)
    {
        abort_unless(array_key_exists($type, self::DEMO_EMAILS_BY_TYPE), 404);

        $user = User::where('email', self::DEMO_EMAILS_BY_TYPE[$type])->firstOrFail();
        Auth::login($user);

        return redirect()->route('item.index');
    }
}
