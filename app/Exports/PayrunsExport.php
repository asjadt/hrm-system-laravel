<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;

class PayrunsExport implements FromView
{
    protected $payruns;

    public function __construct($payruns)
    {
        $this->payruns = $payruns;
    }

    public function view(): View
    {
        return view('export.payruns', ["payruns" => $this->payruns]);
    }

    public function collection()
    {
        
    }
}
