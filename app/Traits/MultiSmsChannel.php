<?php

namespace App\Traits;

trait MultiSmsChannel 
{
    

	public function viaSms()
    {
		$sms = setting('smschannel');
		switch($sms):
			case 'nexmo':
				return 'nexmo';
			case 'SmscRu':
				return \NotificationChannels\SmscRu\SmscRuChannel::class;
			case 'twilio':
				return \NotificationChannels\Twilio\TwilioChannel::class;
			case 'messagebird':
				return \NotificationChannels\Messagebird\MessagebirdChannel::class;
			default :
				return 'nexmo';
			break;
		endswitch;
       
    }
	
	public function toSmscRu($notifiable)
    {
        return \NotificationChannels\SmscRu\SmscRuMessage::create($this->sms($notifiable))
			->from(config('app.name'));
    }
	
	public function toMessagebird($notifiable)
    {
        return (new \NotificationChannels\Messagebird\MessagebirdMessage($this->sms($notifiable)));
    }
	
	public function toTwilio($notifiable)
    {
        return (new TwilioSmsMessage())
            ->content($this->sms($notifiable));
    }
	
	public function toNexmo($notifiable)
	{
		return (new \Illuminate\Notifications\Messages\NexmoMessage)
					->content($this->sms($notifiable))
					->from(config('app.name'));;
	}
	

	
}
