<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Currency;

class CryptoPriceUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'CryptoPrice:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        \Log::info("Cron is working fine!");
     
        /*
           Write your database logic we bellow:
           Item::create(['name'=>'hello new']);
        */
        $this->updateCurrency_table();

        /**
         * 
         * logic ends here
         */
      
        $this->info('CryptoPrice:cron Cummand Run successfully!');
    }
    public function updateCurrency_table(){
        $url='https://bitpay.com/api/rates';
        $json=file_get_contents( $url ) ;
        $dollar=$btc=0;
        $j=json_decode($json);
        $baseprice=0;$rate=0;
        $receiveAmountPrice=0;$fromAmountPrice=0;
        foreach( $j as $obj ){  
            if($obj->code=='USD'){
                $baseprice=$obj->rate;
                $rate=$obj->rate;
                $usdc = ($rate/ $rate)*1;
                Currency::updateOrCreate([
                        'symbol' => 'PUSD',                        
                    ],
                    [   'price' => $usdc,'name' => 'Pay USD']
                );
            break;
            }           
        }
        if($baseprice!=0){
            foreach( $j as $obj ){                
                $usdc = ($rate/$obj->rate)*1;
                Currency::updateOrCreate([
                        'symbol' => $obj->code,
                        'status' => 1,
                        'is_coin' => 1
                    ],
                    [   'price' => $usdc,'name' => $obj->name]
                );
            }
        }
    }
}
