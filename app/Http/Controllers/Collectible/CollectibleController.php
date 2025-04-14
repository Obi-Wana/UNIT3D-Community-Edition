<?php

declare(strict_types=1);

/**
 * NOTICE OF LICENSE.
 *
 * UNIT3D Community Edition is open-sourced software licensed under the GNU Affero General Public License v3.0
 * The details is bundled with this project in the file LICENSE.txt.
 *
 * @project    UNIT3D Community Edition
 *
 * @author     HDVinnie <hdinnovations@protonmail.com>
 * @license    https://www.gnu.org/licenses/agpl-3.0.en.html/ GNU Affero General Public License v3.0
 */

namespace App\Http\Controllers\Collectible;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCollectibleOfferRequest;
use App\Models\CollectibleCategory;
use App\Models\Collectible;
use App\Models\CollectibleItem;
use App\Models\CollectibleOffer;
use App\Models\CollectibleTransaction;
use App\Models\User;

class CollectibleController extends Controller
{
    /**
     * Display All Collectibles.
     */
    public function index(): \Illuminate\Contracts\View\Factory|\Illuminate\View\View
    {
        $collectibleCategories = CollectibleCategory::get();
        $collectibles = Collectible::get();

        return view('collectible.index', [
            'collectibleCategories' => $collectibleCategories,
            'collectibles'          => $collectibles,
        ]);
    }

    /**
     * Display My Collectibles.
     */
    public function myCollectibles(): \Illuminate\Contracts\View\Factory|\Illuminate\View\View
    {
        $user = auth()->user();

        $collectibleCategories = CollectibleCategory::get();
        $collectibles = Collectible::whereHas('items', function ($query) use ($user): void {
            $query->where('user_id', '=', $user->id);
        })
            ->with('category')
            ->get();
        $collectiblesByCategory = $collectibles->groupBy('category_id');

        return view('collectible.my_collectible', [
            'collectibleCategories'  => $collectibleCategories,
            'collectiblesByCategory' => $collectiblesByCategory,
        ]);
    }

    /**
     * Display A Collectible.
     */
    public function show(Collectible $collectible): \Illuminate\Contracts\View\Factory|\Illuminate\View\View
    {
        $user = auth()->user();

        $collectible = Collectible::findOrFail($collectible->id);
        $userOwns = CollectibleItem::where('collectible_id', '=', $collectible->id)->where('user_id', '=', $user->id)->exists();
        $transactions = CollectibleTransaction::where('collectible_id', '=', $collectible->id)->latest()->take(20)->get();
        $offers = CollectibleOffer::where('collectible_id', '=', $collectible->id)->whereNull('filled_when')->get();
        $userIsSelling = CollectibleOffer::where('collectible_id', '=', $collectible->id)->where('user_id', '=', $user->id)->exists();

        return view('collectible.show', [
            'collectible'   => $collectible,
            'userOwns'      => $userOwns,
            'transactions'  => $transactions,
            'offers'        => $offers,
            'userIsSelling' => $userIsSelling
        ]);
    }

    /**
     * Create A Collectible Offer.
     *
     * User creates a new offer.
     */
    public function createOffer(Collectible $collectible): \Illuminate\Contracts\View\Factory|\Illuminate\View\View
    {
        $user = auth()->user();

        $collectibleItem = CollectibleItem::where('collectible_id', '=', $collectible->id)->where('user_id', '=', $user->id)->first();

        return view('collectible.create_offer', [
            'collectibleItem' => $collectibleItem
        ]);
    }

    /**
     * Store A New Collectible Offer.
     *
     * Store User creates a new offer.
     */
    public function storeOffer(Collectible $collectible, StoreCollectibleOfferRequest $request): \Illuminate\Http\RedirectResponse
    {
        $user = auth()->user();

        $collectible = Collectible::findOrFail($collectible->id);
        $collectibleItem = CollectibleItem::where('collectible_id', '=', $collectible->id)->where('user_id', '=', $user->id)->first();
        $userOwns = CollectibleItem::where('collectible_id', '=', $collectible->id)->where('user_id', '=', $user->id)->exists();
        $userIsSelling = CollectibleOffer::where('collectible_id', '=', $collectible->id)->where('user_id', '=', $user->id)->exists();

        if (! $userOwns || $collectible->inStock() || $userIsSelling) {
            return to_route('collectibles.show', ['collectible' => $collectible])
                ->withErrors("You can not sell this item. Either you do not own it, its in stock at the regular market or you are already selling it.");
        }

        $offer = CollectibleOffer::create([
            'collectible_id' => $collectible->id,
            'user_id'        => $user->id,
            'price'          => $request->sell_price,
        ]);

        return to_route('collectibles.show', ['collectible' => $collectible])
            ->with('success', "Offer created.");
    }

    /**
     * Store An Collectible Offer Accept.
     *
     * User accepts an existing offer.
     */
    public function acceptOffer(CollectibleOffer $collectibleOffer): \Illuminate\Http\RedirectResponse
    {
        $user = auth()->user();

        $collectible = $collectibleOffer->collectible;
        $userOwns = CollectibleItem::where('collectible_id', '=', $collectible->id)->where('user_id', '=', $user->id)->exists();

        if ($userOwns) {
            return to_route('collectibles.show', ['collectible' => $collectible])
                ->withErrors("You already own this item.");
        }

        if ($user->id === $collectibleOffer->seller->id) {
            return to_route('collectibles.show', ['collectible' => $collectible])
                ->withErrors("You can not accept your own offer.");
        }

        if ($collectibleOffer->price > $user->seedbonus) {
            return back()->withErrors('Not enough BON.');
        }

        // Get the offered item
        $collectibleItem = CollectibleItem::where('collectible_id', '=', $collectible->id)->where('user_id', '=', $collectibleOffer->seller->id)->first();

        $transaction = CollectibleTransaction::create([
            'collectible_id' => $collectible->id,
            'seller_id'      => $collectibleOffer->seller->id,
            'buyer_id'       => $user->id,
            'price'          => $collectibleOffer->price,
        ]);

        $collectibleItem->update([
            'user_id' => $user->id,
        ]);

        $collectibleOffer->update([
            'filled_when' => now(),
        ]);

        $user->decrement('seedbonus', $collectibleOffer->price);

        return to_route('collectibles.show', ['collectible' => $collectible])
            ->with('success', "Offer accepted.");
    }

    /**
     * Buy A Collectible From Store.
     *
     * User buys in stock collectible.
     */
    public function buy(Collectible $collectible): \Illuminate\Http\RedirectResponse
    {
        $user = auth()->user();

        $userOwns = CollectibleItem::where('collectible_id', '=', $collectible->id)->where('user_id', '=', $user->id)->exists();

        if ($userOwns) {
            return to_route('collectibles.show', ['collectible' => $collectible])
                ->withErrors("You already own this item.");
        }

        if ($collectible->price > $user->seedbonus) {
            return back()->withErrors('Not enough BON.');
        }

        // Get the first available collectible item that is not owned by a user
        $collectibleItem = CollectibleItem::where('collectible_id', '=', $collectible->id)->whereNull('user_id')->first();

        $transaction = CollectibleTransaction::create([
            'collectible_id' => $collectible->id,
            'seller_id'      => User::SYSTEM_USER_ID,
            'buyer_id'       => $user->id,
            'price'          => $collectible->price,
        ]);

        $collectibleItem->update([
            'user_id' => $user->id,
        ]);

        $user->decrement('seedbonus', $collectibleItem->collectible->price);

        return to_route('collectibles.show', ['collectible' => $collectible])
            ->with('success', "Collectible bought");
    }
}
