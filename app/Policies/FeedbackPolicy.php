<?php


namespace App\Policies;

use App\Models\User;
use App\Models\Feedback;
use Illuminate\Auth\Access\HandlesAuthorization;

class FeedbackPolicy
{
    use HandlesAuthorization;
	
	public function before(User $user)
	{
		if ($user->isAdmin()) {
			return true;
		}
	}

    /**
     * Determine whether the user can view the feedback.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Feedback  $feedback
     * @return mixed
     */
    public function view(User $user, Feedback $feedback)
    {
        //
		if( $user->hasPermission('view.feedback'))return true;
		return true;
		
    }

    /**
     * Determine whether the user can create feedback.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
		if( $user->hasPermission('create.feedback'))return true;
		return true;
    }

    /**
     * Determine whether the user can update the feedback.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Feedback  $feedback
     * @return mixed
     */
    public function update(User $user, Feedback $feedback)
    {
        //
		if(!isset($feedback->user_id))return true;
		if( $user->hasPermission('update.feedback'))return true;
		return $user->id == $feedback->user_id;
    }

    /**
     * Determine whether the user can delete the feedback.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Feedback  $feedback
     * @return mixed
     */
    public function delete(User $user, Feedback $feedback)
    {
        //
		if(!isset($feedback->user_id))return true;
		if( $user->hasPermission('delete.feedback'))return true;
		return $user->id == $feedback->user_id;
    }

    /**
     * Determine whether the user can restore the feedback.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Feedback  $feedback
     * @return mixed
     */
    public function restore(User $user, Feedback $feedback)
    {
        //
		if(!isset($feedback->user_id))return true;
		if( $user->hasPermission('restore.feedback'))return true;
		return $user->id == $feedback->user_id;
    }

    /**
     * Determine whether the user can permanently delete the feedback.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Feedback  $feedback
     * @return mixed
     */
    public function forceDelete(User $user, Feedback $feedback)
    {
        //
		if(!isset($feedback->user_id))return true;
		if( $user->hasPermission('forcedelete.feedback'))return true;
		return $user->id == $feedback->user_id;
		
    }
}