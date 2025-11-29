<?php

namespace App\Http\Controllers;

use App\Models\Promotion;

class PromotionController extends Controller
{
    public function show(string $slug)
    {
        $promotion = Promotion::where('slug', $slug)
            ->where('active', true)
            ->where(function ($query) {
                $query->where('valid_from', '<=', now())
                    ->orWhereNull('valid_from');
            })
            ->where(function ($query) {
                $query->where('valid_until', '>=', now())
                    ->orWhereNull('valid_until');
            })
            ->firstOrFail();

        return view('promotions.show', compact('promotion'));
    }
}
