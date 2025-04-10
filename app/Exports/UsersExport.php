<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithBackgroundColor;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Color;


class UsersExport implements FromView
{
    protected $users;

    public function __construct($users)
    {
        $this->users = $users;
    }

    public function view(): View
    {
        return view('export.users', ["users" => $this->users]);
    }



    public function role_string($inputString) {



        $finalString = explode('#', $inputString)[0];


        $finalString = str_replace('business_', '', $finalString);
        $finalString = str_replace('_', '', $finalString);
        return $finalString;
    }

    public function collection()
    {
        if ($this->users instanceof \Illuminate\Support\Collection) {
            $users = $this->users;
        } else {
            $users = collect($this->users->items());
        }

        $result = $users->map(function ($user, $index) {
            return [

                ($user->first_Name . " " . $user->last_Name . " " . $user->last_Name),
                $user->user_id,
                $user->email,
                $user->designation->name,
                $this->role_string($user->roles[0]->name),
                ($user->is_active ? "Active" : "De-active"),
            ];
        });

        return $result;




    }

    public function map($user): array
    {

        return [];
    }

    public function headings(): array
    {
        return [
            ['Employee List:', '', '', '', '', ''],
            ['', '', '', '', '', ''], 
            ['Employee', 'Employee ID', 'Email', 'Designation', 'Role', 'Status'],
        ];
    }



}
