<?php

namespace App\Console\Commands;


use Illuminate\Console\Command;
use App\Traits\BitcoinTrait;

class bchtestnet extends Command
{
	use BitcoinTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bchtestnet:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the BCHTESTNET Transactions and Orders in the blockchain';

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
		$this->coin_cron('BCHTESTNET');
    }
}
