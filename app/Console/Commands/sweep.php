<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Traits\WalletTrait;
class sweep extends Command
{
	use WalletTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sweep:out';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sweep balances to External Addresses';

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
		$this->sweep();
    }
}
