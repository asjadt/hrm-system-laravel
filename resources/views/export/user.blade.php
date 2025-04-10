<!DOCTYPE html>
<html>

<head>
    <title>Employee</title>

    <!--ALL CUSTOM FUNCTIONS -->
    @php

function format_date($date) {
    return \Carbon\Carbon::parse($date)->format('d-m-Y');
}


        function processString($inputString)
        {

            $withoutUnderscore = str_replace('_', '', $inputString);


            $finalString = explode('#', $withoutUnderscore)[0];


            $capitalizedString = ucwords($finalString);

            return $capitalizedString;
        }
    @endphp

    @php

    function time_format($breakHours) {
        if(!$breakHours){
            $breakHours = 0;
        }


$breakSeconds = round($breakHours * 3600);


$formattedBreakTime = gmdate("H:i:s", $breakSeconds);
return $formattedBreakTime;
    }


        $business = auth()->user()->business;



        $created_by = null;
        if (auth()->user()->business) {
            $created_by = auth()->user()->business->created_by;
        }

        $leave_types = \App\Models\SettingLeaveType::where(function ($query) use ($request, $created_by) {
            $query
                ->where('setting_leave_types.business_id', null)
                ->where('setting_leave_types.is_default', 1)
                ->where('setting_leave_types.is_active', 1)
                ->whereDoesntHave('disabled', function ($q) use ($created_by) {
                    $q->whereIn('disabled_setting_leave_types.created_by', [$created_by]);
                })
                ->when(isset($request->is_active), function ($query) use ($request, $created_by) {
                    if (intval($request->is_active)) {
                        return $query->whereDoesntHave('disabled', function ($q) use ($created_by) {
                            $q->whereIn('disabled_setting_leave_types.business_id', [auth()->user()->business_id]);
                        });
                    }
                })
                ->orWhere(function ($query) use ($request, $created_by) {
                    $query
                        ->where('setting_leave_types.business_id', null)
                        ->where('setting_leave_types.is_default', 0)
                        ->where('setting_leave_types.created_by', $created_by)
                        ->where('setting_leave_types.is_active', 1)

                        ->when(isset($request->is_active), function ($query) use ($request) {
                            if (intval($request->is_active)) {
                                return $query->whereDoesntHave('disabled', function ($q) {
                                    $q->whereIn('disabled_setting_leave_types.business_id', [auth()->user()->business_id]);
                                });
                            }
                        });
                })
                ->orWhere(function ($query) use ($request) {
                    $query
                        ->where('setting_leave_types.business_id', auth()->user()->business_id)
                        ->where('setting_leave_types.is_default', 0)
                        ->when(isset($request->is_active), function ($query) use ($request) {
                            return $query->where('setting_leave_types.is_active', intval($request->is_active));
                        });
                });
        })->get();

    

    @endphp
  @php
   $color  = env("FRONT_END_VERSION") == "red"?"#dc2b28" : "#335ff0";
 @endphp

    <style>
        /* Add any additional styling for your PDF */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 10px;
        }

        .table_head_row {
            color: #fff;
            background-color: {{$color}};
            font-weight: 600;
        }

        .table_head_row td {
            color: #fff;
        }

        .table_head_row th,
        tbody tr td {
            text-align: left;
            padding: 10px 0px;
        }

        .table_row {
            background-color: #ffffff;
        }



        .empty_table_row {}

        .table_row td {
            padding: 10px 0px;
            border-bottom: 0.2px solid #ddd;
        }

        .employee {
            color: {{$color}};
            /*font-weight:600;*/
        }

        .logo {
            width: 75px;
            height: 75px;
        }

        .file_title {
            font-size: 1.3rem;
            font-weight: bold;
            text-align: right;
        }

        .business_name {
            font-size: 1.2rem;
            font-weight: bold;
            display: block;
        }

        .business_address {}
    </style>

</head>

<body>

    {{-- PDF HEADING  --}}
    <table style="margin-top:-30px">
        <tbody>
            <tr>
                @if ($business->logo)
                    <td rowspan="2">
                        <img class="logo" src="{{ public_path($business->logo) }}">
                    </td>
                @endif
                <td></td>
            </tr>
            <tr>
                <td class="file_title">Employee Report</td>
            </tr>
            <tr>
                <td>
                    <span class="business_name">{{ $business->name }}</span>

                </td>

            </tr>
        </tbody>
    </table>


    {{-- ALL TABLES  --}}

    {{-- 1. PERSONAL DETAILS  --}}
    @if (!empty($request->employee_details))
    <table>
        <h3>Employee Details</h3>
        <thead>
            <tr class="table_head_row">
                <th>Name</th>
                {{-- <th>First Name</th> --}}
                {{-- <th>Middle Name</th>
                <th>Last Name</th> --}}
                <th>Employe ID</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Gender</th>
            </tr>
        </thead>
        <tbody>
            <tr class="table_row">

                <td>
                    {{ $user->first_Name . " " .  $user->middle_Name . " " . $user->last_Name}}
                </td>
                {{-- <td>
                    {{ $user->first_Name }}
                </td>
                <td>
                    {{ $user->last_Name }}
                </td>
                <td>
                    {{ $user->last_Name }}
                </td> --}}
                <td>
                    {{ $user->user_id }}
                </td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->phone }}</td>
                <td>{{ $user->gender }}</td>
            </tr>
        </tbody>
    </table>

    @endif



    {{-- 2. LEAVE ALLOWANCE  --}}
    @if (!empty($request->leave_allowances))
    <table>
        <h3>Leave Allowances</h3>
        <thead>
            <tr class="table_head_row">

                <th>Allowance Name</th>
                <th>Type</th>
                <th>Allowance</th>
                <th>Earned</th>
                <th>Availability</th>
            </tr>
        </thead>
        <tbody>
            @if (1)
                @foreach ($leave_types as $key => $leave_type)
                    @php
                        $total_recorded_hours = \App\Models\LeaveRecord::whereHas('leave', function ($query) use ($user, $leave_type) {
                            $query->where([
                                'user_id' => $user->id,
                                'leave_type_id' => $leave_type->id,
                            ]);
                        })
                            ->get()
                            ->sum(function ($record) {
                                return \Carbon\Carbon::parse($record->end_time)->diffInHours(\Carbon\Carbon::parse($record->start_time));
                            });
                        $leave_types[$key]->already_taken_hours = $total_recorded_hours;
                    @endphp
                    <tr class="table_row">

                        <td style="text-align: left">
                            {{ $leave_type->name }}
                        </td>
                        <td>{{ $leave_type->type }}</td>
                        <td>{{ time_format($leave_type->amount, 2) }}/ month</td>
                        <td>{{ time_format($leave_type->already_taken_hours, 2) }}</td>
                        <td>{{ time_format($leave_type->amount - $leave_type->already_taken_hours, 2) }}</td>
                    </tr>
                @endforeach

            @endif

        </tbody>
    </table>

@endif

    {{-- 3. ATTENDANCE  --}}
    @if (!empty($request->attendances))
    <table>
        <h3>Attendances</h3>
        <thead>
            <tr class="table_head_row">

                <th>Date</th>

                <th>Start Time</th>
                <th>End Time</th>
                <th>Break (hour)</th>
                <th>Schedule (hour)</th>
                <th>Overtime (hour)</th>
            </tr>
        </thead>
        <tbody>
            @if (count($user->attendances))
                @foreach ($user->attendances as $index => $attendance)
                    <tr class="table_row">

                        <td>{{ format_date($attendance->in_date) }}</td>
                        <td>{{ $attendance->in_time }}</td>
                        <td>{{ $attendance->out_time }}</td>
                        <td>{{ $attendance->does_break_taken?time_format($attendance->break_hours):0 }}</td>
                        <td>{{ time_format($attendance->capacity_hours) }}</td>
                        <td>{{ time_format($attendance->overtime_hours) }}</td>


                    </tr>
                @endforeach


            @endif

        </tbody>
    </table>

@endif


    {{-- 4. LEAVE  --}}
    @if (!empty($request->leaves))
    <table>
        <h3>Leaves</h3>
        <thead>
            <tr class="table_head_row">

                <th>Date AND Time</th>
                <th>Type</th>
                <th>Duration</th>
                <th>Total Leave (hours)</th>
                {{-- <th>Attachment</th> --}}
            </tr>
        </thead>
        <tbody>
            @if (count($user->leaves))

                @foreach ($user->leaves as $index => $leave)



                    <tr class="table_row">

                        <td>{{ format_date($leave->start_date) }} - {{ format_date($leave->end_date) }}   </td>
                        <td>{{ $leave->leave_type?$leave->leave_type->name:"" }}</td>
                        <td>{{ $leave->leave_duration }}</td>


                        <td>
                            @php
                                  $leave->total_leave_hours = $leave->records->sum(function ($record) {
                                $startTime = \Carbon\Carbon::parse($record->start_time);
                                $endTime = \Carbon\Carbon::parse($record->end_time);
                                return $startTime->diffInHours($endTime);

                               });
                            @endphp
                            {{time_format($leave->total_leave_hours)}}



                        </td>

                        {{-- <td>{{ }}</td>
                        <td>{{ $user->is_active ? 'Active' : 'De-active' }}</td> --}}


                    </tr>

                @endforeach

            @endif
        </tbody>
    </table>
@endif

    {{-- 5. DOCUMENTS  --}}
    @if (!empty($request->documents))
    <table>
        <h3>Documents</h3>
        <thead>
            <tr class="table_head_row">

                <th>Title</th>
                {{-- <th>Attachment</th> --}}
                <th>Added by</th>
            </tr>
        </thead>
        <tbody>
            @if (count($user->documents))
                @foreach ($user->documents as $index => $document)
                    <tr class="table_row">

                        <td>{{ $documents->name }}</td>
                        <td>  {{ $documents->creator->first_Name . " " . $documents->creator->last_Name . " " . $documents->creator->middle_Name }}</td>


                    </tr>
                @endforeach

            @endif
        </tbody>
    </table>
@endif


    {{-- 6. ASSETS  --}}
    @if (!empty($request->assets))
    <table>
        <h3>Assets</h3>
        <thead>
            <tr class="table_head_row">

                <th>Asset Name</th>
                <th>Asset Code</th>
                <th>Serial No</th>
                <th>Is Working</th>
                <th>Type</th>
                <th>Date</th>
                <th>Note</th>
            </tr>
        </thead>
        <tbody>
            @if (count($user->assets))
                @foreach ($user->assets as $index => $asset)
                    <tr class="table_row">

                        <td>{{ $asset->name }}</td>
                        <td>{{ $asset->code }}</td>
                        <td>{{ $asset->serial_number }}</td>
                        <td>{{ $asset->is_working }}</td>
                        <td>{{ $asset->type }}</td>
                        <td>{{ format_date($asset->date) }}</td>
                        <td>{{ $asset->note }}</td>

                    </tr>
                @endforeach


            @endif
        </tbody>
    </table>
@endif



    {{-- 7. EDUCATIONAL HISTORY  --}}
    @if (!empty($request->educational_history))
    <table>
        <h3>Educational History</h3>
        <thead>
            <tr class="table_head_row">

                <th>Degree</th>
                <th>Major</th>

                {{-- <th>Institution</th> ETA TO ONEK BOTO HOBE KEMNE SHOW KORAIBA CSS DIA DEIKHO PARO KINA --}}
                <th>Start Date</th>
                <th>Achivments</th>
                {{-- <th>Description</th> ETA TO ONEK BOTO HOBE KEMNE SHOW KORAIBA CSS DIA DEIKHO PARO KINA --}}
            </tr>
        </thead>
        <tbody>
            @if (count($user->education_histories))
                @foreach ($user->education_histories as $index => $education_history)
                    <tr class="table_row">
                        <td style="padding:0px 10px">{{ $index + 1 }}</td>
                        <td>{{ $education_history->degree }}</td>
                        <td>{{ $education_history->major }}</td>
                        <td>{{ format_date($education_history->start_date) }}</td>
                        <td>{{ $education_history->achievements }}</td>

                    </tr>
                @endforeach

            @endif
        </tbody>
    </table>

@endif



    {{-- 8. JOB HISTORY  --}}
    @if (!empty($request->job_history))
    <table>
        <h3>Job History</h3>
        <thead>
            <tr class="table_head_row">

                <th>Job Title</th>
                <th>Company</th>
                <th>Start On</th>
                <th>End At</th>
                <th>Supervisor</th>
                {{-- <th>Contact Info</th> JAGA HOILE DIO EDI --}}
                <th>Country</th>
                {{-- <th>Achivments</th> JAGA HOILE DIO EDI --}}
            </tr>
        </thead>
        <tbody>
            @if (count($user->job_histories))
                @foreach ($user->job_histories as $index => $job_history)
                    <tr class="table_row">


                        <td>{{ $job_history->job_title }}</td>
                        <td>{{ $job_history->company_name }}</td>
                        {{-- <td>{{ \Carbon\Carbon::parse($job_history->employment_start_date)->format('Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($job_history->employment_start_date)->format('F') }}</td> --}}
                        <td>{{ format_date($job_history->employment_start_date) }}</td>
                        <td>{{ format_date($job_history->employment_end_date) }}</td>
                        <td>{{ $job_history->supervisor_name }}</td>
                        <td>{{ $job_history->work_location }}</td>



                    </tr>
                @endforeach

            @endif
        </tbody>
    </table>

@endif



    {{-- 9. IMMIGRATION DERAILS  --}}

    {{-- COS HISTORY --}}
    @if (!empty($request->current_cos_details))
    <table>
        <h3> Current COS Details</h3>
        <thead>
            <tr class="table_head_row">

                {{-- <th>From</th>
                <th>To</th> --}}
                <th>Date Assigned</th>
                <th>Expiry Date</th>
                <th>Certificate Number</th>
                <th>Status</th>
                <th>Note</th>
            </tr>
        </thead>
        <tbody>
            @if (!empty($user->sponsorship_detail))

                    <tr class="table_row">

                        <td>{{ format_date($user->sponsorship_detail->date_assigned) }}</td>
                        <td>{{ format_date($user->sponsorship_detail->expiry_date) }}</td>
                        <td>{{ $user->sponsorship_detail->certificate_number }}</td>
                        <td>{{ $user->sponsorship_detail->current_certificate_status }}</td>
                        <td>{{ $user->sponsorship_detail->note }}</td>
                    </tr>


            @endif
        </tbody>
    </table>
@endif


    {{-- PASSPORT HISTORY --}}
    @if (!empty($request->current_passport_details))
    <table>
        <h3>Current Passport Details</h3>
        <thead>
            <tr class="table_head_row">

                {{-- <th>From</th>
                <th>To</th> --}}
                <th>Issue Date</th>
                <th>Expiry Date</th>
                <th>Passport Number</th>
                <th>Place Of Issue</th>
            </tr>
        </thead>
        <tbody>
            @if (!empty($user->passport_detail))

                    <tr class="table_row">

                        <td>{{ format_date($user->passport_detail->passport_issue_date) }}</td>
                        <td>{{ format_date($user->passport_detail->passport_expiry_date) }}</td>
                        <td>{{ $user->passport_detail->passport_number }}</td>
                        <td>{{ $user->passport_detail->place_of_issue }}</td>
                    </tr>


            @endif
        </tbody>
    </table>
@endif



    {{-- PASSPORT HISTORY --}}
    @if (!empty($request->current_visa_details))
    <table>
        <h3>Current Visa Details</h3>
        <thead>
            <tr class="table_head_row">

                {{-- <th>From</th>
                <th>To</th> --}}
                <th>Issue Date</th>
                <th>Expiry Date</th>
                <th>BRP Number</th>
                <th>Place Of Issue</th>
            </tr>
        </thead>
        <tbody>
            @if (!empty($user->visa_detail))

                    <tr class="table_row">

                        {{-- <td>{{ $user->visa_detail->created_at }}</td>
                        <td>{{ $user->visa_detail->updated_at }}</td> --}}
                        <td>{{ format_date($user->visa_detail->visa_issue_date) }}</td>
                        <td>{{ format_date($user->visa_detail->visa_expiry_date) }}</td>
                        <td>{{ $user->visa_detail->BRP_number }}</td>
                        <td>{{ $user->visa_detail->place_of_issue }}</td>
                    </tr>


            @endif
        </tbody>
    </table>
@endif

@if (!empty($request->current_right_to_works))
<table>
    <h3>Current Right To Works</h3>
    <thead>
        <tr class="table_head_row">

            {{-- <th>From</th>
            <th>To</th> --}}
            <th>Check Date</th>
            <th>Expiry Date</th>
            <th>Code</th>

        </tr>
    </thead>
    <tbody>
        @if (!empty($user->visa_detail))

                <tr class="table_row">
                    {{-- <td>{{ $user->visa_detail->created_at }}</td>
                    <td>{{ $user->visa_detail->updated_at }}</td> --}}
                    <td>{{ format_date($user->right_to_work->right_to_work_check_date) }}</td>
                    <td>{{ format_date($user->right_to_work->right_to_work_expiry_date) }}</td>
                    <td>{{ $user->right_to_work->right_to_work_code }}</td>

                </tr>


        @endif
    </tbody>
</table>
@endif

    {{-- 10. ADDRESS DETAILS  --}}
    @if (!empty($request->address_details))
    <table>
        <h3>Address Details</h3>
        <thead>
            <tr class="table_head_row">

                <th>Address</th>
                <th>City</th>
                <th>Country</th>
                <th>Postcode</th>
            </tr>
        </thead>
        <tbody>
            @if (!empty($user->address_line_1))
                    <tr class="table_row">

                        <td>{{ $user->address_line_1 }} </td>
                        <td>{{ $user->city }}</td>
                        <td>{{ $user->country }}</td>
                        <td>{{ $user->postcode }}</td>
                    </tr>


            @endif
        </tbody>
    </table>
@endif



    {{-- 11. CONTACT  --}}
    @if (!empty($request->contact_details))
    <table>
        <h3>Contact Details</h3>
        <thead>
            <tr class="table_head_row">

                <th>First Name</th>
                <th>Last Name</th>
                <th>Relationship To Employee</th>
                <th>Address</th>
                <th>Postcode</th>
                <th style="text-transform: capitalize">day time tel number</th>
                <th style="text-transform: capitalize">evening time tel number</th>
                <th style="text-transform: capitalize">mobile tel number </th>
            </tr>
        </thead>
        <tbody>
            @if (count($user->emergency_contact_details))
                @foreach (($user->emergency_contact_details) as $index => $emergency_contact)
                    <tr class="table_row">


                        <td>{{ isset($emergency_contact["first_name"]) ? $emergency_contact["first_name"] : '' }}</td>
                        <td>{{ isset($emergency_contact["last_name"]) ? $emergency_contact["last_name"] : '' }}</td>
                        <td>{{ isset($emergency_contact["relationship_of_above_to_you"]) ? $emergency_contact["relationship_of_above_to_you"] : '' }}</td>
                        <td>{{ isset($emergency_contact["address_line_1"]) ? $emergency_contact["address_line_1"] : '' }}</td>
                        <td>{{ isset($emergency_contact["postcode"]) ? $emergency_contact["postcode"] : '' }}</td>
                        <td>{{ isset($emergency_contact["day_time_tel_number"]) ? $emergency_contact["day_time_tel_number"] : '' }}</td>
                        <td>{{ isset($emergency_contact["evening_time_tel_number"]) ? $emergency_contact["evening_time_tel_number"] : '' }}</td>
                        <td>{{ isset($emergency_contact["mobile_tel_number"]) ? $emergency_contact["mobile_tel_number"] : '' }}</td>

                    </tr>
                @endforeach

            @endif
        </tbody>
    </table>

@endif


    {{-- 12. NOTES  --}}
    @if (!empty($request->notes))
    <table>
        <h3>Notes</h3>
        <thead>
            <tr class="table_head_row">

                <th>Title</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            @if (count($user->notes))
                @foreach ($user->notes as $index => $note)
                    <tr class="table_row">

                        <td>{{ $note->title }}</td>
                        <td>{{ $note->description }}</td>
                    </tr>
                @endforeach

            @endif
        </tbody>
    </table>
@endif



    {{-- 13. BANK DETAILS  --}}
    @if (!empty($request->bank_details))
    <table>
        <h3>Bank Details</h3>
        <thead>
            <tr class="table_head_row">

                <th>Bank Name</th>
                <th>Sort Code</th>
                <th>Account Name</th>
                <th>Account Number</th>
            </tr>
        </thead>
        <tbody>
            @if ($user->bank)
                <tr class="table_row">

                    <td>{{ $user->bank->name }}</td>
                    <td>{{ $user->sort_code }}</td>
                    <td>{{ $user->account_name }}</td>
                    <td>{{ $user->account_number }}</td>
                </tr>

            @endif
        </tbody>
    </table>
@endif



    {{-- 14. SOCIAL LINKS  --}}
    @if (!empty($request->social_links))
    <table>
        <h3>Social Links</h3>
        <thead>
            <tr class="table_head_row">

                <th>Website</th>
                <th>URL</th>
            </tr>
        </thead>
        <tbody>
            @if (count($user->social_links))
                @foreach ($user->social_links as $index => $social_link)
                    <tr class="table_row">

                        <td>{{ $social_link->social_site->name }}</td>
                        <td>{{ $social_link->profile_link }}</td>
                    </tr>
                @endforeach

            @endif
        </tbody>
    </table>
@endif


</body>

</html>
