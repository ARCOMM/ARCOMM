<?php

namespace App\Models\Portal;

use Auth;
use Steam;
use Carbon\Carbon;
use Spatie\MediaLibrary\Models\Media;
use App\Models\Portal\SteamAPI;
use App\Models\Missions\Mission;
use App\Models\Operations\Absence;
use App\Models\Permissions\Permission;
use Illuminate\Notifications\Notifiable;
use App\Models\Permissions\PermissionUser;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements HasMedia
{
    use Notifiable;
    use HasMediaTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'steam_id',
        'username',
        'avatar',
        'email'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * Media library image conversions.
     *
     * @return void
     */
    public function registerMediaConversions(Media $media = null) {}

    /**
     * Checks whether the user is a member of the Steam group.
     *
     * @return boolean
     */
    public function isMember()
    {
        if (auth()->guest()) {
            return false;
        }

        $members = (array) SteamAPI::members();
        $members[] = config('steam-auth.webmaster_id');

        return in_array(
            Steam::convertId($this->steam_id, 'id64'),
            $members
        );
    }

    /**
     * Checks whether the user is an admin.
     * Access level must be greater than 1.
     *
     * @return boolean
     */
    public function isAdmin()
    {
        return $this->isMember() && $this->access_level > 1;
    }


    /**
     * Gets the user's missions.
     * Ordered from latest to oldest.
     *
     * @return Collection App\Models\Missions\Mission
     */
    public function missions()
    {
        return Mission::where('user_id', $this->id)->orderBy('created_at', 'desc')->get();
    }

    /**
     * Checks if the user has the given permissions.
     * All permissions must pass true.
     *
     * @return boolean
     */
    public function hasPermissions($names, $only_one = false)
    {
        foreach ($names as $name) {
            $has_permission = $this->hasPermission($name);

            if ($has_permission && $only_one) {
                return true;
            } else if (!$has_permission) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks if the user has the given permission.
     *
     * @return boolean
     */
    public function hasPermission($name)
    {
        $permission = Permission::where('name', $name)->first();

        if (!$permission) {
            $permission = Permission::create(['name' => $name]);
            return false;
        }

        return !is_null(
            PermissionUser::where('user_id', $this->id)
                ->where('permission_id', $permission->id)
                ->first()
        );
    }

    /**
     * Gets all mission testers.
     *
     * @return Collection App\Models\Portal\User
     */
    public function missionTesters($ignore_auth = true)
    {
        return static::all()->filter(function($user) {
            if ($ignore_auth) {
                return $user->hasPermission('mission:notes');
            } else {
                // TODO
                return $user->hasPermission('mission:notes');
            }
        });
    }

    /**
     * Gets all unregistered users from the Steam group.
     *
     * @return Collection Steam API User Summaries
     */
    public static function unregistered()
    {
        $users = static::all();
        $filtered = [];

        foreach (SteamAPI::members() as $id) {
            if (!$users->contains('steam_id', $id)) {
                $filtered[] = $id;
            }
        }

        return collect(Steam::user($filtered)->GetPlayerSummaries());
    }

    /**
     * Gets all absence announcements for the future.
     *
     * @return Collection App\Models\Operations\Absence
     */
    public function absences()
    {
        return Absence::where('user_id', $this->id)->get()->reject(function($absence) {
            $absence->operation->starts_at >= Carbon::now();
        });
    }
}
