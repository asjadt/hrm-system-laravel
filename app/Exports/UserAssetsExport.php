<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;

class UserAssetsExport implements FromView
{

    /**
    * @return \Illuminate\Support\Collection
    */
    protected $user_assets;

    public function __construct($user_assets)
    {
        $this->user_assets = $user_assets;
    }

    public function view(): View
    {
        return view('export.user_assets', ["user_assets" => $this->user_assets]);
    }




    public function collection()
    {
        
    }
}
