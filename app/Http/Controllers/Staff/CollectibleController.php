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
 * @author     Roardom <roardom@protonmail.com>
 * @license    https://www.gnu.org/licenses/agpl-3.0.en.html/ GNU Affero General Public License v3.0
 */

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\StoreCollectibleRequest;
use App\Http\Requests\Staff\UpdateCollectibleRequest;
use App\Models\Collectible;
use App\Models\CollectibleCategory;
use App\Models\CollectibleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class CollectibleController extends Controller
{
    /**
     * Display All Collectibles.
     */
    public function index(): \Illuminate\Contracts\View\Factory|\Illuminate\View\View
    {
        $collectibleCategories = CollectibleCategory::get();
        $collectibles = Collectible::get();

        return view('Staff.collectible.index', [
            'collectibleCategories' => $collectibleCategories,
            'collectibles'          => $collectibles,
        ]);
    }

    /**
     * Show Collectible Create Form.
     */
    public function create(Request $request): \Illuminate\Contracts\View\Factory|\Illuminate\View\View
    {
        return view('Staff.collectible.create', [
            'collectibleCategoryId' => $request->integer('collectibleCategoryId'),
            'collectibleCategories' => CollectibleCategory::orderBy('name')->get(),
        ]);
    }

    /**
     * Store A New Collectible Item.
     */
    public function store(StoreCollectibleRequest $request): \Illuminate\Http\RedirectResponse
    {
        // Add the icon
        if ($request->hasFile('icon')) {
            $icon = $request->file('icon');

            abort_if(\is_array($icon), 400);

            if (!\in_array($icon->getClientOriginalExtension(), ['jpg', 'JPG', 'jpeg', 'bmp', 'png', 'PNG', 'tiff'])) {
                return to_route('users.show', ['user' => $user])
                    ->withErrors('Only .jpg, .bmp, .png and .tiff are allowed.');
            }

            if ($icon->getSize() > config('image.max_upload_size')) {
                return to_route('users.show', ['user' => $user])
                    ->withErrors('Your avatar is too large, max file size: '.(config('image.max_upload_size') / 1_000_000).' MB');
            }

            $filename = 'collectibles-'.uniqid('', true).'.'.$icon->getClientOriginalExtension();
            $path = Storage::disk('collectible-icons')->path($filename);
            Image::make($icon->getRealPath())->fit(100, 100)->encode('png', 100)->save($path);
        }

        // Create the collectible
        $collectible = Collectible::create(['icon' => $filename ?? null] + $request->validated());

        // Create the collectible items based on max_amount.
        // The collectible item is not linked to any users, means its
        // available to be bought from the store (not user-user trade).
        for ($i = 1; $i <= $request->input('max_amount'); $i++) {
            $collectibleItemsData[] = [
                'collectible_id' => $collectible->id,
            ];
        }
        CollectibleItem::insert($collectibleItemsData);

        return to_route('staff.collectibles.index')
            ->with('success', 'Item has been created successfully.');
    }

    /**
     * Collectible Edit Form.
     */
    public function edit(Collectible $collectible): \Illuminate\Contracts\View\Factory|\Illuminate\View\View
    {
        return view('Staff.collectible.edit', [
            'collectible'           => $collectible,
            'collectibleCategories' => CollectibleCategory::orderBy('name')->get(),
            'max_amount'            => CollectibleItem::where('collectible_id', '=', $collectible->id)->count(),
        ]);
    }

    /**
     * Edit A Collectible.
     */
    public function update(UpdateCollectibleRequest $request, Collectible $collectible): \Illuminate\Http\RedirectResponse
    {
        if ($request->hasFile('icon')) {
            $icon = $request->file('icon');

            abort_if(\is_array($icon), 400);

            $filename = 'collectibles-'.uniqid('', true).'.'.$icon->getClientOriginalExtension();
            $path = Storage::disk('collectible-icons')->path($filename);
            Image::make($icon->getRealPath())->fit(75, 75)->encode('png', 100)->save($path);

            $collectible->update(['icon' => $filename ?? null] + $request->validated());
        } else {
            $oldIcon = $collectible->icon;
            $collectible->update(['icon' => $oldIcon ?? null] + $request->validated());
        }

        return to_route('staff.collectibles.index')
            ->with('success', 'Item has been updated successfully.');
    }
}
