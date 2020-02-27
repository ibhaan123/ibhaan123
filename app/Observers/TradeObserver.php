<?php
namespace App\Observers;

use App\Events\TradeEvent;
use App\Notifications\NewTrade;
use App\Models\Trade;

class TradeObserver
{
    public function saved(Trade $trade)
    {
		$trade->load(['user','trader']);
		$trade->user->notify(new NewTrade($trade)); // user balance and tables
		$trade->trader->notify(new NewTrade($trade)); // user balance and tables
    }
	
}
?>