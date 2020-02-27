<?php


namespace App\Policies;

use App\Models\User;
use App\Models\Trade;
use Illuminate\Auth\Access\HandlesAuthorization;

class TradePolicy
{
    use HandlesAuthorization;
	
	public function before(User $user)
	{
		if ($user->isAdmin()) {
			return true;
		}
	}

    /**
     * Determine whether the user can view the trade.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Trade  $trade
     * @return mixed
     */
    public function view(User $user, Trade $trade)
    {
        //
		if( $user->hasPermission('view.trade'))return true;
		return $trade->user_id == $user->id || $trade->ad_user_id == $user->id;
		
    }

    /**
     * Determine whether the user can create trades.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
		return true;
    }
	
	
	 public function feedback(User $user, Trade $trade)
    {
        //
		if( $user->hasPermission('update.trade'))return true;
		return $trade->user_id == $user->id||$trade->ad_user_id == $user->id;
    }

    /**
     * Determine whether the user can update the trade.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Trade  $trade
     * @return mixed
     */
    public function update(User $user, Trade $trade)
    {
        //
		if( $user->hasPermission('update.trade'))return true;
		return $trade->user_id == $user->id || $trade->ad_user_id == $user->id;
    }

    /**
     * Determine whether the user can delete the trade.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Trade  $trade
     * @return mixed
     */
    public function delete(User $user, Trade $trade)
    {
        //
		if( $user->hasPermission('delete.trade'))return true;
		return false;
    }

    /**
     * Determine whether the user can restore the trade.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Trade  $trade
     * @return mixed
     */
    public function restore(User $user, Trade $trade)
    {
        //

		if( $user->hasPermission('restore.trade'))return true;
		return false;
    }

    /**
     * Determine whether the user can permanently delete the trade.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Trade  $trade
     * @return mixed
     */
    public function forceDelete(User $user, Trade $trade)
    {
        //
		if( $user->hasPermission('forcedelete.trade'))return true;
		return false;
    }
}