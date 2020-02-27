<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Traits\WalletTrait;
class withdrawals extends Command
{
	use WalletTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'withdrawals:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process Withdrawal Queue For BTC family';

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
		$this->process_withdrawals();
    }
}
