<?php
namespace App\Observers;
use App\Notifications\NewService_tx;
use App\Models\Service_tx;

class ServiceTxObserver
{
    public function created(Service_tx $Service_tx)
    {
		//$Service_tx->user->notify(new NewService_tx($Service_tx));
		
    }
}
?>