<?php

namespace App\Console\Commands;

use App\Models\Holiday;
use Illuminate\Console\Command;

class HolidayRepeatScheduler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'holiday:renew';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command holiday renew';

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



        $holidays = Holiday::where("repeats_annually", 1)->get()->each(function($item) {
            $item->start_date = \Carbon\Carbon::parse($item->start_date)->addYear();
            $item->end_date = \Carbon\Carbon::parse($item->end_date)->addYear();

            $item->save();
        });
        return 0;





    }
}
