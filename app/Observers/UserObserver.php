<?php

namespace App\Observers;

use App\Models\User;
use App\Models\UserPreference;

class UserObserver
{
    /**
     * Handle the User "created" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function created(User $user): void
    {
        UserPreference::create([
            'user_id' => $user->id,
            'notice_method' => $user->email ? 'E-mail' : 'SMS',
            'city' => null,
            'meteorological_warnings' => false,
            'hydrological_warnings' => false,
            'temperature_warning' => false,
            'temperature_check_value' => 10,
        ]);
    }
}
