<?php


namespace App\Policies;

use App\Models\User;
use App\Models\Ad;
use Illuminate\Auth\Access\HandlesAuthorization;

class AdPolicy
{
    use HandlesAuthorization;
	
	public function before(User $user)
	{
		if ($user->isAdmin()) {
			return true;
		}
	}

    /**
     * Determine whether the user can view the ad.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Ad  $ad
     * @return mixed
     */
    public function view(User $user, Ad $ad)
    {
        //
		return true;
		
    }

    /**
     * Determine whether the user can create ads.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
		return true;
    }

    /**
     * Determine whether the user can update the ad.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Ad  $ad
     * @return mixed
     */
    public function update(User $user, Ad $ad)
    {
		if( $user->hasPermission('update.ad'))return true;
		return $user->id == $ad->user_id;
    }

    /**
     * Determine whether the user can delete the ad.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Ad  $ad
     * @return mixed
     */
    public function delete(User $user, Ad $ad)
    {
        //
		if( $user->hasPermission('delete.ad'))return true;
		return $user->id == $ad->user_id;
    }

    /**
     * Determine whether the user can restore the ad.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Ad  $ad
     * @return mixed
     */
    public function restore(User $user, Ad $ad)
    {
        //
		if( $user->hasPermission('restore.ad'))return true;
		return false;
    }

    /**
     * Determine whether the user can permanently delete the ad.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Ad  $ad
     * @return mixed
     */
    public function forceDelete(User $user, Ad $ad)
    {
        //
		if( $user->hasPermission('forcedelete.ad'))return true;
		return false;
		
    }
}