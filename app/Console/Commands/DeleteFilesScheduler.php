<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class DeleteFilesScheduler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'files:delete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'delete files';

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

        Log::info('Task executed.');
        $location =  config("setup-config.temporary_files_location");
        $folderPath = public_path($location);


        $currentDateTime = Carbon::now();


        $files = File::files($folderPath);

        foreach ($files as $file) {
            $fileCreatedAt = Carbon::createFromTimestamp(File::lastModified($file));
            $differenceInDays = $currentDateTime->diffInDays($fileCreatedAt);

            if ($differenceInDays >= 2) {
         
                File::delete($file);
            }
        }
    }
}
