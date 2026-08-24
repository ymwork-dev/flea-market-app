<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use App\Http\Requests\PurchaseRequest;

class PurchaseController extends Controller
{
    public function showPurchasePage($item_id)
    {
        $item = Item::findOrFail($item_id);
        $user = Auth::user();

        if (session('last_item_id') != $item_id) {
            session()->forget('payment_method');
        }

        session(['last_item_id' => $item_id]);

        return view('purchase', compact('item', 'user'));
    }

    public function purchase(PurchaseRequest $request, $item_id)
    {
        $item = Item::findOrFail($item_id);
        $user = Auth::user();

        Stripe::setApiKey(config('services.stripe.secret'));

        $session = Session::create([
            'payment_method_types' => ['card', 'konbini'],
            'line_items' => [[
                'price_data' => [
                    'currency'     => 'jpy',
                    'product_data' => ['name' => $item->name],
                    'unit_amount'  => $item->price,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('purchase.success', ['item_id' => $item->id]),
            'cancel_url'  => route('purchase.show', ['item_id' => $item->id]),
        ]);

        session(['shipping_address' => [
            'postcode' => $user->postcode,
            'address'  => $user->address,
            'building' => $user->building,
        ]]);

        return redirect($session->url);
    }

    public function success($item_id)
    {
        $item = Item::findOrFail($item_id);
        $user = Auth::user();
        $shipping = session('shipping_address');

        $item->update(['is_sold' => true]);

        Order::create([
            'user_id'  => $user->id,
            'item_id'  => $item->id,
            'postcode' => $shipping['postcode'] ?? $user->postcode,
            'address'  => $shipping['address'] ?? $user->address,
            'building' => $shipping['building'] ?? $user->building,
        ]);

        session()->forget(['payment_method', 'shipping_address']);

        return redirect('/')->with('message', '商品を購入しました');
    }

    public function editAddress($item_id)
    {
        $item = Item::findOrFail($item_id);
        $user = Auth::user();
        return view('purchase_address', compact('item', 'user'));
    }

    public function updateAddress(\App\Http\Requests\AddressRequest $request, $item_id)
    {
        $user = Auth::user();
        $user->update([
            'postcode' => $request->postcode,
            'address'  => $request->address,
            'building' => $request->building,
        ]);

        return redirect()->route('purchase.show', ['item_id' => $item_id]);
    }

    public function storePaymentSession(Request $request)
    {
        session(['payment_method' => $request->payment_method]);
        return response()->json(['success' => true]);
    }
}
