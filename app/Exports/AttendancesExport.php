<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;

class AttendancesExport implements FromView
{

    protected $attendances;

    public function __construct($attendances)
    {
        $this->attendances = $attendances;
    }

    public function view(): View
    {
        return view('export.attendances', ["attendances" => $this->attendances]);
    }





    public function role_string($inputString) {

        $withoutUnderscore = str_replace('_', '', $inputString);


        $finalString = explode('#', $withoutUnderscore)[0];


        $rolePart = str_replace('business_', '', $finalString);

        return $rolePart;
    }

    public function collection()
    {
        if ($this->users instanceof \Illuminate\Support\Collection) {

            return collect($this->users)->map(function ($user, $index) {
                return [

                    ($user->first_Name ." " . $user->last_Name . " " . $user->last_Name ),
                    $user->user_id,
                    $user->email,
                    $user->designation->name,
                    $this->role_string($user->roles[0]->name),
                    ($user->is_active ? "Active":"De-active")


                ];
            });





        } else {
            return collect($this->users->items())->map(function ($user, $index) {
                return [

                    ($user->first_Name ." " . $user->last_Name . " " . $user->last_Name ),
                    $user->user_id,
                    $user->email,
                    $user->designation->name,
                    $this->role_string($user->roles[0]->name),
                    ($user->is_active ? "Active":"De-active")


                ];
            });

        }


    }

    public function map($user): array
    {

        return [];
    }

    public function headings(): array
    {
        $headings = [
            'Employee',
            'Employee ID',
            'Email',
            'Designation',
            'Role',
            'Status',
        ];

    
        array_unshift($headings, "Employee Report" . ':');

        return $headings;

    }
}
