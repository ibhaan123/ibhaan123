<?php

namespace App\Models;


use App\Models\Trade;
use Musonza\Chat\Models\MessageNotification as msg;

class Message extends msg
{
	public function trade()
    {
        return $this->belongsTo(Trade::class, 'conversation_id', 'chat_id');
    }
	
	public function message(){
		 return $this->belongsTo(\Musonza\Chat\Models\Message::class, 'message_id', 'id');
	}
}
