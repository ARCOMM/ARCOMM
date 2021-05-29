<?php

namespace App\Models\Missions;

use App\Models\Portal\User;
use Illuminate\Database\Eloquent\Model;

class MissionComment extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'text',
        'user_id',
        'published',
        'mission_id',
        'created_at',
        'updated_at',
    ];

    /**
     * Gets the author of the comment.
     *
     * @return App\Models\Portal\User
     */
    public function user()
    {
        return $this->belongsTo('App\Models\Portal\User');
    }

    /**
     * Gets the mission the comment belongs to.
     *
     * @return App\Models\Missions\Mission
     */
    public function mission()
    {
        return $this->belongsTo('App\Models\Missions\Mission');
    }

    /**
     * Checks whether the comment belongs to the authenticated user.
     *
     * @return boolean
     */
    public function isMine()
    {
        return $this->user_id == auth()->user()->id;
    }
}
