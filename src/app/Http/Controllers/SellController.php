<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ExhibitionRequest;

// 出品に関する処理を行うコントローラー
class SellController extends Controller
{
    // 出品画面を表示するメソッド
    public function sell()
    {
        // カテゴリ情報を全て取得
        $categories = Category::all();
        // 出品と編集を見分け、出品画面の目印
        $isEdit = false;

        // 出品画面にカテゴリと出品の目印を渡して表示
        return view('item_sell', compact('categories', 'isEdit'));
    }

    // 出品の入力チェックを行い保存するメソッド
    public function store(ExhibitionRequest $request)
    {
        // 画像ファイルがある場合、保存してパスを取得。画像が無い場合は空
        $path = null;
        if ($request->hasFile('img_url')) {
            $path = $request->file('img_url')->store('items', 'public');
        }

        // 新しい商品を作成してitemsテーブルに保存
        $item = Item::create([
            // 出品者IDにログイン中ユーザーのIDを保存
            'user_id' => Auth::id(),
            // 商品名を保存
            'name' => $request->name,
            // 入力された価格の保存
            'price' => $request->price,
            // 商品説明を保存
            'description' => $request->description,
            // 画像パスがある場合保存。無ければ空
            'img_url' => $path,
            // 商品状態を保存
            'condition' => $request->condition,
            // ブランド名の保存
            'brand' => $request->brand,
            // 販売中フラグを保存
            'is_sold' => false,
        ]);

        // カテゴリが選択されている場合、商品とカテゴリを紐付ける
        if ($request->category_ids) {
            $item->categories()->attach($request->category_ids);
        }

        // メッセージの表示と一緒にトップページへリダイレクト
        return redirect('/')->with('message', '商品を出品しました');
    }

    // 商品編集画面を表示するメソッド
    public function edit($item_id)
    {
        // 商品IDから商品情報を1件取得。存在しない場合は404エラー
        $item = Item::findOrFail($item_id);
        // 自分が出品した商品か、販売中か確認。違う場合403エラー
        $this->ensureEditable($item);

        // カテゴリ情報を全て取得
        $categories = Category::all();
        // 商品に紐づくカテゴリIDを取得
        $selectedCategoryIds = $item->categories->pluck('id')->toArray();
        // 出品画面との見分けのため、編集画面の目印
        $isEdit = true;

        // 商品編集画面に商品情報、カテゴリ情報、選択済みカテゴリID、編集画面の目印を渡して表示
        return view('item_sell', compact('item', 'categories', 'selectedCategoryIds', 'isEdit'));
    }

    // 出品した商品の編集を更新するためのメソッド
    public function update(ExhibitionRequest $request, $item_id)
    {
        // 商品IDから商品情報を1件取得、無い場合404エラー
        $item = Item::findOrFail($item_id);
        // 出品した商品が販売中か確認、違う場合403エラー
        $this->ensureEditable($item);

        // 商品画像のパスを取得
        $path = $item->img_url;
        // 画像ファイルの更新がある場合、保存し、画像ファイルのパスを取得
        if ($request->hasFile('img_url')) {
            $path = $request->file('img_url')->store('items', 'public');
        }

        // 商品の更新をitemデータベースに保存
        $item->update([
            // 商品名の更新
            'name' => $request->name,
            // 商品価格
            'price' => $request->price,
            // 商品説明
            'description' => $request->description,
            // 画像パス
            'img_url' => $path,
            // 商品状態
            'condition' => $request->condition,
            // ブランド名
            'brand' => $request->brand,
        ]);

        // 商品情報のカテゴリIDを選び直したものに入れ替えて保存。未選択は全て外す。
        $item->categories()->sync($request->category_ids ?? []);

        // メッセージの表示と一緒に、商品詳細ページにリダイレクト
        return redirect()->route('item.show', ['item_id' => $item->id])->with('message', '商品を編集しました');
    }

    // 出品商品を削除するためのメソッド
    public function destroy($item_id)
    {
        // 商品IDから商品情報を1件取得、無ければ404エラー
        $item = Item::findOrFail($item_id);
        // 出品した商品が販売中かの確認、違う場合403エラー
        $this->ensureEditable($item);

        // itemデータベースから選択した商品情報の削除
        $item->delete();

        // メッセージと一緒にトップ画面へリダイレクト
        return redirect('/')->with('message', '商品を削除しました');
    }

    // 出品した商品が販売中か、編集・削除してもいいか確確認するためのメソッド
    private function ensureEditable(Item $item): void
    {
        // 商品の出品者がログイン中ユーザーでない場合403エラー
        abort_if($item->user_id !== Auth::id(), 403);
        // 売切れの場合、403エラーにメッセージ表示
        abort_if($item->is_sold, 403, '売却済みの商品は編集・削除できません');
    }
}
