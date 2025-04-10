<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;

class LeavesExport implements FromView
{
    protected $leaves;

    public function __construct($leaves)
    {
        $this->leaves = $leaves;
    }

    public function view(): View
    {
        return view('export.leaves', ["leaves" => $this->leaves]);
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        
    }
}
