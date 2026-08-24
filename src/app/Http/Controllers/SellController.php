<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ExhibitionRequest;

class SellController extends Controller
{
    public function sell()
    {
        $categories = Category::all();

        return view('item_sell', compact('categories'));
    }

    public function store(ExhibitionRequest $request)
    {
        $path = null;
        if ($request->hasFile('img_url')) {
            $path = $request->file('img_url')->store('items', 'public');
        }

        $item = Item::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'price' => $request->price,
            'description' => $request->description,
            'img_url' => $path,
            'condition' => $request->condition,
            'brand' => $request->brand,
            'is_sold' => false,
        ]);

        if ($request->category_ids) {
            $item->categories()->attach($request->category_ids);
        }

        return redirect('/')->with('message', '商品を出品しました');
    }
}
