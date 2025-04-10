<?php

namespace App\Exports;



use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;



class WorkShiftsExport implements FromView
{
    protected $work_shifts;

    public function __construct($work_shifts)
    {
        $this->work_shifts = $work_shifts;
    }

    public function view(): View
    {
        return view('export.work_shifts', ["work_shifts" => $this->work_shifts]);
    }


}
