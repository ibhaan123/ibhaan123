<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Models\Trade;
class TradeEvent implements ShouldBroadcast 
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
	
	public $id;
	public $type;
	public $row;
	public $user;
	public $trader;
	public $pair;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Trade $trade)
    {
       
		$this->id = $trade->id;
		$this->type = $trade->type;
		$this->user = $trade->user_id;
		$this->trader = $trade->ad_user_id;
		$this->pair = $trade->pair;
		$this->row = $this->buildRow($trade);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel($this->pair);
    }
	
	private function buildRow($hist){
		$qd = $hist->market->quote->decimals;
		$bd = $hist->market->base->decimals;
		$color = $hist->type =="sell"?' text-danger':' text-success';
		$row = '<tr id="trh'.$hist->id.'">
			<td width="25%" class=" text-bright"">'.$hist->created_at->toTimeString().'</td>
            <td width="10%" class="text-center '.$color.'">'.strtoupper($hist->type).'</td>
			<td width="35%" class="text-right '.$color.'" >'.number_format($hist->price, $qd > 7?8:2).'</td>
			<td width="35%" class="text-right '.$color.'">'.number_format($hist->qty,$bd > 7?8:2).'</td>
            </tr>';
		return $row;
						
	}
	
	

}
