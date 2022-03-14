<?php
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
 * @credits    clandestine8 <https://github.com/clandestine8>
 */

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AutoRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:roles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate ';

    //private $users;
    private $rules;
    //private $roles;

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        foreach (\App\Models\AutoRoles::latest()->get() as $rule) {
            if ($rule->enabled) {
                $query = [];
                if ($rule->buffer || $rule->download || $rule->upload || $rule->ratio || $rule->accountAge) {
                    $query = DB::table('users')->select('id')
                        ->when($rule->buffer && $rule->bufferMin !== null, function ($query) use ($rule) {
                            return $query->whereRaw('uploaded / '.config('other.ratio').' - downloaded >= '.$rule->bufferMin);
                        })
                        ->when($rule->buffer && $rule->bufferMax !== null, function ($query) use ($rule) {
                            return $query->whereRaw('uploaded / '.config('other.ratio').' - downloaded <= '.$rule->bufferMax);
                        })
                        ->when($rule->download && $rule->downloadMin !== null, function ($query) use ($rule) {
                            return $query->whereRaw('downloaded >= '.$rule->downloadMin);
                        })
                        ->when($rule->download && $rule->downloadMax !== null, function ($query) use ($rule) {
                            return $query->whereRaw('downloaded <= '.$rule->downloadMax);
                        })
                        ->when($rule->upload && $rule->uploadMin !== null, function ($query) use ($rule) {
                            return $query->whereRaw('uploaded >= '.$rule->uploadMin);
                        })
                        ->when($rule->upload && $rule->uploadMax !== null, function ($query) use ($rule) {
                            return $query->whereRaw('uploaded <= '.$rule->uploadMax);
                        })
                        ->when($rule->ratio && $rule->ratioMin !== null, function ($query) use ($rule) {
                            return $query->whereRaw('uploaded / downloaded >= '.$rule->ratioMin);
                        })
                        ->when($rule->ratio && $rule->ratioMax !== null, function ($query) use ($rule) {
                            return $query->whereRaw('uploaded / downloaded <= '.$rule->ratioMax);
                        })
                        ->when($rule->accountAge && $rule->accountAgeMin !== null, function ($query) use ($rule) {
                            return $query->whereRaw('DATEDIFF(NOW(), created_at) >= '.$rule->accountAgeMin);
                        })
                        ->when($rule->accountAge && $rule->accountAgeMax !== null, function ($query) use ($rule) {
                            return $query->whereRaw('DATEDIFF(NOW(), created_at) <= '.$rule->accountAgeMax);
                        })->pluck('id')->toArray();
                }

                if ($rule->uploadCount) {
                    $uploadCount = User::withCount('torrents')
                        ->when($rule->uploadCountMin !== null, function ($query) use ($rule) {
                            $query->having('torrents_count', '>=', $rule->uploadCountMin);
                        })
                        ->when($rule->uploadCountMax !== null, function ($query) use ($rule) {
                            $query->having('torrents_count', '>=', $rule->uploadCountMax);
                        })->pluck('id')->toArray();

                    if (! empty($query)) {
                        $query = array_intersect($query, $uploadCount);
                    } else {
                        $query = $uploadCount;
                    }
                }

                if ($rule->downloadCount) {
                    $this->info('Download Count for Rule '.$rule->id);
                    $downloadCount = DB::table('history')
                        ->select('user_id')->distinct()
                        ->where('actual_downloaded', '>', 0)->pluck('user_id')->toArray();

                    foreach ($downloadCount as $id) {
                        $this->info('User ID: '.$id);
                    }

                    if (! empty($query)) {
                        $query = array_intersect($query, $downloadCount);
                    } else {
                        $query = $downloadCount;
                    }
                }

                $users = User::whereIn('id', array_values($query))->get();
                switch ($rule->type) {
                    case 'give':
                        foreach ($users as $user) {
                            if (! $user->hasRole($rule->roles->slug)) {
                                $user->roles()->attach($rule->roles);
                            }
                        }
                        break;
                    case 'remove':
                        foreach ($users as $user) {
                            if ($user->hasRole($rule->roles->slug)) {
                                $user->roles()->detach($rule->roles);
                            }
                        }
                        break;
                }
            }
        }
    }
}