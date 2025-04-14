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
 * App\Models\CollectibleItemRequirement.
 *
 * @property int                             $id
 * @property int                             $collectible_id
 * @property int|null                        $min_uploaded
 * @property int|null                        $min_seedsize
 * @property int|null                        $min_avg_seedtime
 * @property float|null                      $min_ratio
 * @property int|null                        $min_age
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class CollectibleRequirement extends Model
{
    use HasFactory;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    /**
     * A Requirement Belongs To A Collectible.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<CollectibleItem>
     */
    public function collectible(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Collectible::class, 'collectible_id');
    }
}
