<?php

namespace App\Jobs;

use App\Http\Utils\ErrorUtil;
use App\Http\Utils\PayrunUtil;
use App\Models\Attendance;
use App\Models\AttendanceArrear;
use App\Models\Department;
use App\Models\Holiday;
use App\Models\LeaveRecord;
use App\Models\LeaveRecordArrear;
use App\Models\Payroll;
use App\Models\Payrun;
use App\Models\User;
use App\Models\WorkShift;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayrunJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, PayrunUtil,ErrorUtil;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {








            try {




            $payruns = Payrun::where('is_active', true)->get();


            $payruns->each(function ($payrun)  {
                $employees = User::where([
                    "business_id" => $payrun->business_id,
                    "is_active" => 1
                ])




                    ->get();
                $this->process_payrun($payrun,$employees,$payrun->start_date,$payrun->end_date,false,true);
            });

        } catch (Exception $e) {

            $this->storeError($e, 422, $e->getLine(), $e->getFile());
        }

           

    }
}
