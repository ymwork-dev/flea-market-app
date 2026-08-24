<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Http\Requests\ItemSearchRequest;
use Illuminate\Support\Facades\Auth;

class ItemController extends Controller
{
    public function index(ItemSearchRequest $request)
    {
        $user = Auth::user();
        if ($user) {
            if (!$user->hasVerifiedEmail()) {
                return redirect()->route('verification.notice');
            }

            if (empty($user->postcode)) {
                return redirect('/mypage/profile');
            }
        }

        $tab = $request->getTab();
        $keyword = $request->getKeyword();
        $query = Item::query();

        if ($tab === 'mylist') {
            if ($user) {
                $query = $user->likedItems()->where('items.user_id', '!=', $user->id);
            } else {
                $query->where('id', 0);
            }
        } else {
            if ($user) {
                $query->where('user_id', '!=', $user->id);
            }
        }

        if ($keyword) {
            $query->where('name', 'LIKE', '%' . $keyword . '%');
        }

        $items = $query->get();

        return view('index', [
            'items' => $items,
            'tab' => $tab,
            'keyword' => $keyword
        ]);
    }

    public function show($item_id)
    {
        $user = Auth::user();

        if ($user) {
            if (!$user->hasVerifiedEmail()) {
                return redirect()->route('verification.notice');
            }

            if (empty($user->postcode)) {
                return redirect('/mypage/profile');
            }
        }

        $item = Item::findOrFail($item_id);

        return view('item_detail', compact('item'));
    }
}
