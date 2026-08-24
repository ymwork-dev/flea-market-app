<?php

namespace App\Http\Controllers;

use App\Models\Item;

class LikeController extends Controller
{
    public function toggleLike($item_id)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $user->likedItems()->toggle($item_id);

        $is_liked = $user->likedItems()->where('item_id', $item_id)->exists();

        $like_count = \App\Models\Item::findOrFail($item_id)->likedByUsers()->count();

        return response()->json([
            'is_liked' => $is_liked,
            'like_count' => $like_count
        ]);
    }
}
