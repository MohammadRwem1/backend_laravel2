<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }
    public function update(User $user,Apartment $apartment){
        return $user->id === $apartment->owner_id;
    }

    public function delete(User $user,Apartment $apartment){
        return $user->id === $apartment->owner_id;
    }
}
