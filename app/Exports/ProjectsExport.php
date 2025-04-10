<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;

class ProjectsExport implements FromView
{
    protected $projects;

    public function __construct($projects)
    {
        $this->projects = $projects;
    }

    public function view(): View
    {
        return view('export.projects', ["projects" => $this->projects]);
    }

    public function collection()
    {
        
    }
}
