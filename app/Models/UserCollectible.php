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
 * App\Models\UserCollectible.
 *
 * @property int                             $id
 * @property int                             $collectible_id
 * @property int                             $user_id
 * @property bool                            $active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class UserCollectible extends Model
{
    use HasFactory;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    /**
     * A User Has Many Active Items.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<CollectibleItemRequirement>
     */
    public function collectible(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CollectibleItem::class, 'collectible_id');
    }
}
