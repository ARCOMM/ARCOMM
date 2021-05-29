<?php

namespace App\Models\Missions;

use App\Models\Portal\User;
use App\Models\Missions\Mission;
use Illuminate\Database\Eloquent\Model;

class MissionNote extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'mission_id',
        'text'
    ];

    /**
     * Gets the user model the note belongs to.
     *
     * @return App\Models\Portal\User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Gets the mission model the note belongs to.
     *
     * @return App\Models\Missions\Mission
     */
    public function mission()
    {
        return $this->belongsTo(Mission::class);
    }

    /**
     * Checks if the note is mine.
     *
     * @return boolean
     */
    public function isMine()
    {
        return $this->user_id == auth()->user()->id;
    }
}
