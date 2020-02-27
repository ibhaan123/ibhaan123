<?php
namespace App\Logic\Gateways;

interface Gateway {
	//protected $view ;
	//protected $gate;
	//protected $sendHash;
	//protected $collectHash ;
	public function payout( );
	public function form_validation( );
	public function collect();
	public function ipn();
	public function form();
	public function isRedirect();
	public function getView();
	public function redirect();
	
	
}