<?php

namespace App\Traits;

trait Paths
{
	protected $userDashboard = '/';
	protected $home = '/home';
	protected $adminDashboard = '/admin';
	protected $redirectAfterLogout = '/';
	
	public function redirectAuthenticated() {
		$user = \Auth::user();
		switch(true) {
			case $user->isAdmin():
			case $user->isSuperAdmin():
				$adminUrl = property_exists($this, 'adminDashboard') ? $this->adminDashboard : '/admin';
				return $adminUrl;
				break;
			default:
				$userUrl = property_exists($this, 'userDashboard') ? $this->userDashboard: '/';
				return $userUrl;
		}
	}
}
