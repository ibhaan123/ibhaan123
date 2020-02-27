<?php

namespace App\Console\Commands;


use Illuminate\Console\Command;
use App\Traits\CronTrait;

class tokens extends Command
{
	use CronTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tokens:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the Token Data in the DB';

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
		$this->cryptoCompare();
    }
}
