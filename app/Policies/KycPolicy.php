<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Kyc;
use Illuminate\Auth\Access\HandlesAuthorization;

class KycPolicy
{
    use HandlesAuthorization;
	
	public function before($user, $ability)
	{
		if ($user->isAdmin()) {
			return true;
		}
	}
    /**
     * Determine whether the user can view the kyc.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Kyc  $kyc
     * @return mixed
	 
     */
    public function view(User $user, Kyc $kyc)
    {
        //
		return $user->id == $kyc->user_id;
    }

    /**
     * Determine whether the user can create kycs.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user )
    {
        //
		return true;
    }

    /**
     * Determine whether the user can update the kyc.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Kyc  $kyc
     * @return mix;ed
     */
    public function update(User $user, Kyc $kyc)
    {
        //
		return $user->id == $kyc->user_id;
    }

    /**
     * Determine whether the user can delete the kyc.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Kyc  $kyc
     * @return mixed
     */
    public function delete(User $user, Kyc $kyc)
    {
        //
		return $user->id == $kyc->user_id;
    }
}
