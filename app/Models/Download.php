<?php

namespace App\Models;

use App\Models\User;
use App\Models\Track;
use Illuminate\Database\Eloquent\Model;

class Download extends Model
{
    protected $fillable = ['user_id', 'track_id', 'ip_address', 'user_agent'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function track()
    {
        return $this->belongsTo(Track::class);
    }
}
