<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;

class EmployeeSchedulesExport implements FromView
{
    protected $employees;
    public function __construct($employees)
    {
        $this->employees = $employees;
    }

    public function view(): View
    {
        return view('export.employee-schedule', ["employees" => $this->employees]);
    }
}
