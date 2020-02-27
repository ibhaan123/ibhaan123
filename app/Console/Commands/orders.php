<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Traits\WalletTrait;
class orders extends Command
{
	use WalletTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Orders and Market deposits';

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
        //run the cron
		$this->ordersCron();
	
		
    }
}
