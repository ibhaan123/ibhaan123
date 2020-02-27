<?php

namespace App\Console\Commands;


use Illuminate\Console\Command;
use App\Traits\BitcoinTrait;

class btgtestnet extends Command
{
	use BitcoinTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'btgtestnet:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the BTG Testnet Transactions and Orders in the blockchain';

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
        //
		$this->coin_cron('BTGTESTNET');
    }
}
