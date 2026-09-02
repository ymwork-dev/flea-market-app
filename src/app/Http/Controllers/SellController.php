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
        $isEdit = false;

        return view('item_sell', compact('categories', 'isEdit'));
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

    public function edit($item_id)
    {
        $item = Item::findOrFail($item_id);
        $this->ensureEditable($item);

        $categories = Category::all();
        $selectedCategoryIds = $item->categories->pluck('id')->toArray();
        $isEdit = true;

        return view('item_sell', compact('item', 'categories', 'selectedCategoryIds', 'isEdit'));
    }

    public function update(ExhibitionRequest $request, $item_id)
    {
        $item = Item::findOrFail($item_id);
        $this->ensureEditable($item);

        $path = $item->img_url;
        if ($request->hasFile('img_url')) {
            $path = $request->file('img_url')->store('items', 'public');
        }

        $item->update([
            'name' => $request->name,
            'price' => $request->price,
            'description' => $request->description,
            'img_url' => $path,
            'condition' => $request->condition,
            'brand' => $request->brand,
        ]);

        // sync を使うと、選び直したカテゴリーだけが残るように入れ替えてくれる
        $item->categories()->sync($request->category_ids ?? []);

        return redirect()->route('item.show', ['item_id' => $item->id])->with('message', '商品を編集しました');
    }

    public function destroy($item_id)
    {
        $item = Item::findOrFail($item_id);
        $this->ensureEditable($item);

        $item->delete();

        return redirect('/')->with('message', '商品を削除しました');
    }

    // 自分が出品した、かつ、まだ売れていない商品だけ編集・削除できるようにする
    private function ensureEditable(Item $item): void
    {
        abort_if($item->user_id !== Auth::id(), 403);
        abort_if($item->is_sold, 403, '売却済みの商品は編集・削除できません');
    }
}
