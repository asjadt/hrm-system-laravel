<?php

namespace App\Console\Commands;

use App\Jobs\PayrunJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PayrunScheduler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payrun:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'payrun';

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
     * @return int
     */
    public function handle()
    {
        Log::info("Payrun Job is not running");
        dispatch(new PayrunJob());
        return 0;
    }
}
