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

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\CollectibleItem.
 *
 * @property int                             $id
 * @property int                             $collection_id
 * @property string                          $name
 * @property string                          $description
 * @property bool                            $resell
 * @property float                           $price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Collectible extends Model
{
    use HasFactory;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    /**
     * Belongs To A Collection Category.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<CollectibleCategory>
     */
    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CollectibleCategory::class);
    }

    /**
     * An Item Has Many Requirements.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<CollectibleRequirement>
     */
    public function requirements(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CollectibleRequirement::class, 'collectible_id');
    }

    /**
     * An Item Has Many Items.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<CollectibleRequirement>
     */
    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CollectibleItem::class, 'collectible_id');
    }

    /**
     * An Item Is In Stock.
     */
    public function inStock()
    {
        $available = $this->items()->whereNull('user_id')->count();

        return $available > 0;
    }
}
