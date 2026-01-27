<?php

namespace App\Services;

use App\Models\Activity;

class ActivityLogger
{
    public static function log($user_id, $type, $description)
    {
        Activity::create([
            'user_id' => $user_id,
            'type' => $type,
            'description' => $description,
            'ip_address' => request()->ip(),
        ]);
    }
}
