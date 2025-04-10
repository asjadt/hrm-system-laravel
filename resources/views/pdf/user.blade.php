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

        .index_col {
            width: 5px;
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
                    <address class="business_address">{{ $business->address_line_1 }}</address>
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
                @if (!empty($request->employee_details_name))
                <th>Name</th>
                @endif
                @if (!empty($request->employee_details_user_id))
                <th>Employe ID</th>
                @endif
                @if (!empty($request->employee_details_email))
                <th>Email</th>
                @endif
                @if (!empty($request->employee_details_phone))
                <th>Phone</th>
                @endif
                @if (!empty($request->employee_details_gender))
                <th>Gender</th>
                @endif
            </tr>
        </thead>
        <tbody>
            <tr class="table_row">

                @if (!empty($request->employee_details_name))
                    <td>{{ $user->first_Name . " " .  $user->middle_Name . " " . $user->last_Name }}</td>
                @endif
                @if (!empty($request->employee_details_user_id))
                    <td>{{ $user->user_id }}</td>
                @endif
                @if (!empty($request->employee_details_email))
                    <td>{{ $user->email }}</td>
                @endif
                @if (!empty($request->employee_details_phone))
                    <td>{{ $user->phone }}</td>
                @endif
                @if (!empty($request->employee_details_gender))
                    <td>{{ $user->gender }}</td>
                @endif
            </tr>
        </tbody>
    </table>

    @endif



    {{-- 2. LEAVE ALLOWANCE  --}}
    @if (!empty($request->leave_allowances) && count($leave_types))
    <table>
        <h3>Leave Allowances</h3>
        <thead>
            <tr class="table_head_row">
                <th class="index_col"></th>
                @if (!empty($request->leave_allowance_name))
                    <th>Allowance Name</th>
                @endif
                @if (!empty($request->leave_allowance_type))
                    <th>Type</th>
                @endif
                @if (!empty($request->leave_allowance_allowance))
                    <th>Allowance</th>
                @endif
                @if (!empty($request->leave_allowance_earned))
                    <th>Earned</th>
                @endif
                @if (!empty($request->leave_allowance_availability))
                    <th>Availability</th>
                @endif
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
                        <td class="index_col">{{ $key + 1 }}</td>
                        @if (!empty($request->leave_allowance_name))
                            <td style="text-align: left">{{ $leave_type->name }}</td>
                        @endif
                        @if (!empty($request->leave_allowance_type))
                            <td>{{ $leave_type->type }}</td>
                        @endif
                        @if (!empty($request->leave_allowance_allowance))
                            <td>{{ time_format($leave_type->amount, 2) }}/ month</td>
                        @endif
                        @if (!empty($request->leave_allowance_earned))
                            <td>{{ time_format($leave_type->already_taken_hours, 2) }}</td>
                        @endif
                        @if (!empty($request->leave_allowance_availability))
                            <td>{{ time_format($leave_type->amount - $leave_type->already_taken_hours, 2) }}</td>
                        @endif
                    </tr>
                @endforeach
            @else
            <tr>
                <td colspan="8" style="text-align: center;">No Data Found</td>
            </tr>
            @endif

        </tbody>
    </table>

@endif

    {{-- 3. ATTENDANCE  --}}
    @if (!empty($request->attendances) && count($user->attendances))
    <table>
        <h3>Attendances</h3>
        <thead>
            <tr class="table_head_row">
                <th class="index_col"></th>
                @if (!empty($request->attendance_date))
                    <th>Date</th>
                @endif
                @if (!empty($request->attendance_start_time))
                    <th>Start Time</th>
                @endif
                @if (!empty($request->attendance_end_time))
                    <th>End Time</th>
                @endif
                @if (!empty($request->attendance_break))
                    <th>Break (hour)</th>
                @endif
                @if (!empty($request->attendance_schedule))
                    <th>Schedule (hour)</th>
                @endif
                @if (!empty($request->attendance_overtime))
                    <th>Overtime (hour)</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @if (1)
                @foreach ($user->attendances as $index => $attendance)
                    <tr class="table_row">
                        <td class="index_col">{{ $index + 1 }}</td>
                        @if (!empty($request->attendance_date))
                            <td>{{ format_date($attendance->in_date) }}</td>
                        @endif
                        @if (!empty($request->attendance_start_time))
                            <td>{{ $attendance->in_time }}</td>
                        @endif
                        @if (!empty($request->attendance_end_time))
                            <td>{{ $attendance->out_time }}</td>
                        @endif
                        @if (!empty($request->attendance_break))
                            <td>{{ $attendance->does_break_taken ? time_format($attendance->break_hours) : 0 }}</td>
                        @endif
                        @if (!empty($request->attendance_schedule))
                            <td>{{ time_format($attendance->capacity_hours) }}</td>
                        @endif
                        @if (!empty($request->attendance_overtime))
                            <td>{{ time_format($attendance->overtime_hours) }}</td>
                        @endif
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="8" style="text-align: center;">No Data Found</td>
                </tr>
            @endif
        </tbody>
    </table>
@endif



    {{-- 4. LEAVE  --}}
    @if (!empty($request->leaves) && count($user->leaves))
    <table>
        <h3>Leaves</h3>
        <thead>
            <tr class="table_head_row">
                <th class="index_col"></th>
                @if (!empty($request->leave_date_time))
                    <th>Date & Time</th>
                @endif
                @if (!empty($request->leave_type))
                    <th>Type</th>
                @endif
                @if (!empty($request->leave_duration))
                    <th>Duration</th>
                @endif
                @if (!empty($request->total_leave_hours))
                    <th>Total Leave (hours)</th>
                @endif
                {{-- <th>Attachment</th> --}}
            </tr>
        </thead>
        <tbody>
            @if (1)
                @foreach ($user->leaves as $index => $leave)
                    <tr class="table_row">
                        <td class="index_col">{{ $index + 1 }}</td>
                        @if (!empty($request->leave_date_time))
                            <td>{{ format_date($leave->start_date) }} - {{ format_date($leave->end_date) }}</td>
                        @endif
                        @if (!empty($request->leave_type))
                            <td>{{ $leave->leave_type ? $leave->leave_type->name : "" }}</td>
                        @endif
                        @if (!empty($request->leave_duration))
                            <td>{{ $leave->leave_duration }}</td>
                        @endif
                        @if (!empty($request->total_leave_hours))
                            <td>
                                @php
                                    $leave->total_leave_hours = $leave->records->sum(function ($record) {
                                        $startTime = \Carbon\Carbon::parse($record->start_time);
                                        $endTime = \Carbon\Carbon::parse($record->end_time);
                                        return $startTime->diffInHours($endTime);
                                    });
                                @endphp
                                {{ time_format($leave->total_leave_hours) }}
                            </td>
                        @endif
                        {{-- <td>{{ }}</td>
                        <td>{{ $user->is_active ? 'Active' : 'De-active' }}</td> --}}
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="8" style="text-align: center;">No Data Found</td>
                </tr>
            @endif
        </tbody>
    </table>
@endif


    {{-- 5. DOCUMENTS  --}}
    @if (!empty($request->documents) && count($user->documents))
    <table>
        <h3>Documents</h3>
        <thead>
            <tr class="table_head_row">
                <th class="index_col"></th>
                @if (!empty($request->document_title))
                    <th>Title</th>
                @endif
                {{-- <th>Attachment</th> --}}
                @if (!empty($request->document_added_by))
                    <th>Added by</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @if (1)
                @foreach ($user->documents as $index => $document)
                    <tr class="table_row">
                        <td class="index_col">{{ $index + 1 }}</td>
                        @if (!empty($request->document_title))
                            <td>{{ $document->name }}</td>
                        @endif
                        @if (!empty($request->document_added_by))
                            <td>{{ $document->creator->first_Name . " " . $document->creator->last_Name . " " . $document->creator->middle_Name }}</td>
                        @endif
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="8" style="text-align: center;">No Data Found</td>
                </tr>
            @endif
        </tbody>
    </table>
@endif



    {{-- 6. ASSETS  --}}
    @if (!empty($request->assets) && count($user->assets))
    <table>
        <h3>Assets</h3>
        <thead>
            <tr class="table_head_row">
                <th class="index_col"></th>
                @if (!empty($request->asset_name))
                    <th>Asset Name</th>
                @endif
                @if (!empty($request->asset_code))
                    <th>Asset Code</th>
                @endif
                @if (!empty($request->asset_serial_number))
                    <th>Serial No</th>
                @endif
                @if (!empty($request->asset_is_working))
                    <th>Is Working</th>
                @endif
                @if (!empty($request->asset_type))
                    <th>Type</th>
                @endif
                @if (!empty($request->asset_date))
                    <th>Date</th>
                @endif
                @if (!empty($request->asset_note))
                    <th>Note</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @if (1)
                @foreach ($user->assets as $index => $asset)
                    <tr class="table_row">
                        <td class="index_col">{{ $index + 1 }}</td>
                        @if (!empty($request->asset_name))
                            <td>{{ $asset->name }}</td>
                        @endif
                        @if (!empty($request->asset_code))
                            <td>{{ $asset->code }}</td>
                        @endif
                        @if (!empty($request->asset_serial_number))
                            <td>{{ $asset->serial_number }}</td>
                        @endif
                        @if (!empty($request->asset_is_working))
                            <td>{{ $asset->is_working }}</td>
                        @endif
                        @if (!empty($request->asset_type))
                            <td>{{ $asset->type }}</td>
                        @endif
                        @if (!empty($request->asset_date))
                            <td>{{ format_date($asset->date) }}</td>
                        @endif
                        @if (!empty($request->asset_note))
                            <td>{{ $asset->note }}</td>
                        @endif
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="8" style="text-align: center;">No Data Found</td>
                </tr>
            @endif
        </tbody>
    </table>
@endif




    {{-- 7. EDUCATIONAL HISTORY  --}}
    @if (!empty($request->educational_history) && count($user->education_histories))
    <table>
        <h3>Educational History</h3>
        <thead>
            <tr class="table_head_row">
                <th class="index_col"></th>
                @if (!empty($request->educational_history_degree))
                    <th>Degree</th>
                @endif
                @if (!empty($request->educational_history_major))
                    <th>Major</th>
                @endif
                {{-- Uncomment if you want to include institution --}}
                {{-- <th>Institution</th> --}}
                @if (!empty($request->educational_history_start_date))
                    <th>Start Date</th>
                @endif
                @if (!empty($request->educational_history_achievements))
                    <th>Achievements</th>
                @endif
                {{-- Uncomment if you want to include description --}}
                {{-- <th>Description</th> --}}
            </tr>
        </thead>
        <tbody>
            @if (1)
                @foreach ($user->education_histories as $index => $education_history)
                    <tr class="table_row">
                        <td style="padding:0px 10px">{{ $index + 1 }}</td>
                        @if (!empty($request->educational_history_degree))
                            <td>{{ $education_history->degree }}</td>
                        @endif
                        @if (!empty($request->educational_history_major))
                            <td>{{ $education_history->major }}</td>
                        @endif
                        {{-- Uncomment if you want to include institution --}}
                        {{-- <td>{{ $education_history->institution }}</td> --}}
                        @if (!empty($request->educational_history_start_date))
                            <td>{{ format_date($education_history->start_date) }}</td>
                        @endif
                        @if (!empty($request->educational_history_achievements))
                            <td>{{ $education_history->achievements }}</td>
                        @endif
                        {{-- Uncomment if you want to include description --}}
                        {{-- <td>{{ $education_history->description }}</td> --}}
                    </tr>
                @endforeach
            @else
            <tr>
                <td colspan="8" style="text-align: center;">No Data Found</td>
            </tr>
            @endif
        </tbody>
    </table>
@endif




    {{-- 8. JOB HISTORY  --}}
    @if (!empty($request->job_history) && count($user->job_histories))
    <table>
        <h3>Job History</h3>
        <thead>
            <tr class="table_head_row">
                <th class="index_col"></th>
                @if (!empty($request->job_history_job_title))
                    <th>Job Title</th>
                @endif
                @if (!empty($request->job_history_company))
                    <th>Company</th>
                @endif
                @if (!empty($request->job_history_start_on))
                    <th>Start On</th>
                @endif
                @if (!empty($request->job_history_end_at))
                    <th>End At</th>
                @endif
                @if (!empty($request->job_history_supervisor))
                    <th>Supervisor</th>
                @endif
                {{-- <th>Contact Info</th> JAGA HOILE DIO EDI --}}
                @if (!empty($request->job_history_country))
                    <th>Country</th>
                @endif
                {{-- <th>Achivments</th> JAGA HOILE DIO EDI --}}
            </tr>
        </thead>
        <tbody>
            @if (1)
                @foreach ($user->job_histories as $index => $job_history)
                    <tr class="table_row">
                        <td class="index_col">{{ $index + 1 }}</td>

                        @if (!empty($request->job_history_job_title))
                            <td>{{ $job_history->job_title }}</td>
                        @endif
                        @if (!empty($request->job_history_company))
                            <td>{{ $job_history->company_name }}</td>
                        @endif
                        {{-- <td>{{ \Carbon\Carbon::parse($job_history->employment_start_date)->format('Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($job_history->employment_start_date)->format('F') }}</td> --}}
                        @if (!empty($request->job_history_start_on))
                            <td>{{ format_date($job_history->employment_start_date) }}</td>
                        @endif
                        @if (!empty($request->job_history_end_at))
                            <td>{{ format_date($job_history->employment_end_date) }}</td>
                        @endif
                        @if (!empty($request->job_history_supervisor))
                            <td>{{ $job_history->supervisor_name }}</td>
                        @endif
                        @if (!empty($request->job_history_country))
                            <td>{{ $job_history->work_location }}</td>
                        @endif

                    </tr>
                @endforeach
            @else
            <tr>
                <td colspan="8" style="text-align: center;">No Data Found</td>
            </tr>
            @endif
        </tbody>
    </table>

@endif




    {{-- 9. IMMIGRATION DERAILS  --}}

    {{-- COS HISTORY --}}
    @if (!empty($request->current_cos_details) && !empty($user->sponsorship_detail))
    <table>
        <h3> Current COS Details</h3>
        <thead>
            <tr class="table_head_row">
                <th class="index_col"></th>
                @if (!empty($request->current_cos_details_date_assigned))
                    <th>Date Assigned</th>
                @endif
                @if (!empty($request->current_cos_details_expiry_date))
                    <th>Expiry Date</th>
                @endif
                @if (!empty($request->current_cos_details_certificate_number))
                    <th>Certificate Number</th>
                @endif
                @if (!empty($request->current_cos_details_current_certificate_status))
                    <th>Status</th>
                @endif
                @if (!empty($request->current_cos_details_note))
                    <th>Note</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @if (1)
                <tr class="table_row">
                    <td class="index_col">1</td>
                    @if (!empty($request->current_cos_details_date_assigned))
                        <td>{{ format_date($user->sponsorship_detail->date_assigned) }}</td>
                    @endif
                    @if (!empty($request->current_cos_details_expiry_date))
                        <td>{{ format_date($user->sponsorship_detail->expiry_date) }}</td>
                    @endif
                    @if (!empty($request->current_cos_details_certificate_number))
                        <td>{{ $user->sponsorship_detail->certificate_number }}</td>
                    @endif
                    @if (!empty($request->current_cos_details_current_certificate_status))
                        <td>{{ $user->sponsorship_detail->current_certificate_status }}</td>
                    @endif
                    @if (!empty($request->current_cos_details_note))
                        <td>{{ $user->sponsorship_detail->note }}</td>
                    @endif
                </tr>
            @else
                <tr>
                    <td colspan="8" style="text-align: center;">No Data Found</td>
                </tr>
            @endif
        </tbody>
    </table>
@endif



@if (!empty($request->current_pension_details) && !empty($user->pension_detail))
@if (!$user->pension_detail->pension_eligible)


<table>
    <h3> Current Pension Details</h3>
    <thead>
        <tr class="table_head_row">
            <th class="index_col"></th>
            @if (!empty($request->current_pension_details_pension_scheme_status))
                <th>Status</th>
            @endif
            @if (!empty($request->current_pension_details_pension_enrollment_issue_date))
                <th>Issue Date</th>
            @endif
            @if (!empty($request->current_pension_details_pension_scheme_opt_out_date))
                <th>Opt Out Date</th>
            @endif
            @if (!empty($request->current_pension_details_pension_re_enrollment_due_date))
                <th>Re Enrollment Due Date</th>
            @endif

        </tr>
    </thead>
    <tbody>
        @if (1)
            <tr class="table_row">
                <td class="index_col">1</td>
                @if (!empty($request->current_pension_details_pension_scheme_status))
                    <td>{{ format_date($user->pension_detail->pension_scheme_status) }}</td>
                @endif
                @if (!empty($request->current_pension_details_pension_enrollment_issue_date))
                    <td>{{ format_date($user->pension_detail->pension_enrollment_issue_date) }}</td>
                @endif
                @if (!empty($request->current_pension_details_pension_scheme_opt_out_date))
                    <td>{{ $user->pension_detail->pension_scheme_opt_out_date }}</td>
                @endif
                @if (!empty($request->current_pension_details_pension_re_enrollment_due_date))
                    <td>{{ $user->pension_detail->pension_re_enrollment_due_date }}</td>
                @endif

            </tr>
        @else
            <tr>
                <td colspan="8" style="text-align: center;">No Data Found</td>
            </tr>
        @endif
    </tbody>
</table>
@endif
@endif




    {{-- PASSPORT HISTORY --}}
    @if (!empty($request->current_passport_details) && !empty($user->passport_detail))
    <table>
        <h3>Current Passport Details</h3>
        <thead>
            <tr class="table_head_row">
                <th class="index_col"></th>
                @if (!empty($request->current_passport_details_issue_date))
                    <th>Issue Date</th>
                @endif
                @if (!empty($request->current_passport_details_expiry_date))
                    <th>Expiry Date</th>
                @endif
                @if (!empty($request->current_passport_details_passport_number))
                    <th>Passport Number</th>
                @endif
                @if (!empty($request->current_passport_details_place_of_issue))
                    <th>Place Of Issue</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @if (1)
                <tr class="table_row">
                    <td class="index_col">1</td>
                    @if (!empty($request->current_passport_details_issue_date))
                        <td>{{ format_date($user->passport_detail->passport_issue_date) }}</td>
                    @endif
                    @if (!empty($request->current_passport_details_expiry_date))
                        <td>{{ format_date($user->passport_detail->passport_expiry_date) }}</td>
                    @endif
                    @if (!empty($request->current_passport_details_passport_number))
                        <td>{{ $user->passport_detail->passport_number }}</td>
                    @endif
                    @if (!empty($request->current_passport_details_place_of_issue))
                        <td>{{ $user->passport_detail->place_of_issue }}</td>
                    @endif
                </tr>
            @else
                <tr>
                    <td colspan="8" style="text-align: center;">No Data Found</td>
                </tr>
            @endif
        </tbody>
    </table>
@endif




    {{-- VISA HISTORY --}}
    @if (!empty($request->current_visa_details) && !empty($user->visa_detail))
    <table>
        <h3>Current Visa Details</h3>
        <thead>
            <tr class="table_head_row">
                <th class="index_col"></th>
                @if (!empty($request->current_visa_details_issue_date))
                    <th>Issue Date</th>
                @endif
                @if (!empty($request->current_visa_details_expiry_date))
                    <th>Expiry Date</th>
                @endif
                @if (!empty($request->current_visa_details_brp_number))
                    <th>BRP Number</th>
                @endif
                @if (!empty($request->current_visa_details_place_of_issue))
                    <th>Place Of Issue</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @if (1)
                <tr class="table_row">
                    <td class="index_col">1</td>
                    @if (!empty($request->current_visa_details_issue_date))
                        <td>{{ format_date($user->visa_detail->visa_issue_date) }}</td>
                    @endif
                    @if (!empty($request->current_visa_details_expiry_date))
                        <td>{{ format_date($user->visa_detail->visa_expiry_date) }}</td>
                    @endif
                    @if (!empty($request->current_visa_details_brp_number))
                        <td>{{ $user->visa_detail->BRP_number }}</td>
                    @endif
                    @if (!empty($request->current_visa_details_place_of_issue))
                        <td>{{ $user->visa_detail->place_of_issue }}</td>
                    @endif
                </tr>
            @else
                <tr>
                    <td colspan="8" style="text-align: center;">No Data Found</td>
                </tr>
            @endif
        </tbody>
    </table>
@endif


@if (!empty($request->current_right_to_works) && !empty($user->right_to_work))
<table>
    <h3>Current Right To Works</h3>
    <thead>
        <tr class="table_head_row">
            <th class="index_col">1</th>
            @if (!empty($request->current_right_to_works_right_to_work_check_date))
            <th>Check Date</th>
        @endif
        @if (!empty($request->current_right_to_works_right_to_work_expiry_date))
        <th>Expiry Date</th>
    @endif
    @if (!empty($request->current_right_to_works_right_to_work_code))
    <th>Code</th>
    @endif




        </tr>
    </thead>
    <tbody>
        @if (1)

                <tr class="table_row">
                    {{-- <td>{{ $user->visa_detail->created_at }}</td>
                    <td>{{ $user->visa_detail->updated_at }}</td> --}}
                    <td class="index_col">1</td>



                    @if (!empty($request->current_right_to_works_right_to_work_check_date))
                    <td>{{ format_date($user->right_to_work->right_to_work_check_date) }}</td>
                    @endif


                    @if (!empty($request->current_right_to_works_right_to_work_expiry_date))
                    <td>{{ format_date($user->right_to_work->right_to_work_expiry_date) }}</td>
                    @endif

                    @if (!empty($request->current_right_to_works_right_to_work_code))
                    <td>{{ $user->right_to_work->right_to_work_code }}</td>
                    @endif

                </tr>


        @endif
    </tbody>
</table>
@endif




    {{-- 10. ADDRESS DETAILS  --}}
    @if (!empty($request->address_details) && !empty($user->address_line_1))
    <table>
        <h3>Address Details</h3>
        <thead>
            <tr class="table_head_row">
                <th class="index_col"></th>
                @if (!empty($request->address_details_address))
                    <th>Address</th>
                @endif
                @if (!empty($request->address_details_city))
                    <th>City</th>
                @endif
                @if (!empty($request->address_details_country))
                    <th>Country</th>
                @endif
                @if (!empty($request->address_details_postcode))
                    <th>Postcode</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @if (1)
                <tr class="table_row">
                    <td class="index_col">1</td>
                    @if (!empty($request->address_details_address))
                        <td>{{ $user->address_line_1 }}</td>
                    @endif
                    @if (!empty($request->address_details_city))
                        <td>{{ $user->city }}</td>
                    @endif
                    @if (!empty($request->address_details_country))
                        <td>{{ $user->country }}</td>
                    @endif
                    @if (!empty($request->address_details_postcode))
                        <td>{{ $user->postcode }}</td>
                    @endif
                </tr>
            @else
                <tr>
                    <td colspan="8" style="text-align: center;">No Data Found</td>
                </tr>
            @endif
        </tbody>
    </table>
@endif




    {{-- 11. CONTACT  --}}
    @if (!empty($request->contact_details) && count($user->emergency_contact_details))
    <table>
        <h3>Contact Details</h3>
        <thead>
            <tr class="table_head_row">
                <th class="index_col"></th>
                @if (!empty($request->contact_details_first_name))
                    <th>First Name</th>
                @endif
                @if (!empty($request->contact_details_last_name))
                    <th>Last Name</th>
                @endif
                @if (!empty($request->contact_details_relationship))
                    <th>Relationship To Employee</th>
                @endif
                @if (!empty($request->contact_details_address))
                    <th>Address</th>
                @endif
                @if (!empty($request->contact_details_postcode))
                    <th>Postcode</th>
                @endif
                @if (!empty($request->contact_details_day_time_tel_number))
                    <th style="text-transform: capitalize">Day Time Tel Number</th>
                @endif
                @if (!empty($request->contact_details_evening_time_tel_number))
                    <th style="text-transform: capitalize">Evening Time Tel Number</th>
                @endif
                @if (!empty($request->contact_details_mobile_tel_number))
                    <th style="text-transform: capitalize">Mobile Tel Number</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @if (1)
                @foreach (($user->emergency_contact_details) as $index => $emergency_contact)
                @if (!empty($emergency_contact["mobile_tel_number"]))
                <tr class="table_row">
                    <td class="index_col">{{ $index + 1 }}</td>

                    @if (!empty($request->contact_details_first_name))
                        <td>{{ isset($emergency_contact["first_name"]) ? $emergency_contact["first_name"] : '' }}</td>
                    @endif
                    @if (!empty($request->contact_details_last_name))
                        <td>{{ isset($emergency_contact["last_name"]) ? $emergency_contact["last_name"] : '' }}</td>
                    @endif
                    @if (!empty($request->contact_details_relationship))
                        <td>{{ isset($emergency_contact["relationship_of_above_to_you"]) ? $emergency_contact["relationship_of_above_to_you"] : '' }}</td>
                    @endif
                    @if (!empty($request->contact_details_address))
                        <td>{{ isset($emergency_contact["address_line_1"]) ? $emergency_contact["address_line_1"] : '' }}</td>
                    @endif
                    @if (!empty($request->contact_details_postcode))
                        <td>{{ isset($emergency_contact["postcode"]) ? $emergency_contact["postcode"] : '' }}</td>
                    @endif
                    @if (!empty($request->contact_details_day_time_tel_number))
                        <td>{{ isset($emergency_contact["day_time_tel_number"]) ? $emergency_contact["day_time_tel_number"] : '' }}</td>
                    @endif
                    @if (!empty($request->contact_details_evening_time_tel_number))
                        <td>{{ isset($emergency_contact["evening_time_tel_number"]) ? $emergency_contact["evening_time_tel_number"] : '' }}</td>
                    @endif
                    @if (!empty($request->contact_details_mobile_tel_number))
                        <td>{{ isset($emergency_contact["mobile_tel_number"]) ? $emergency_contact["mobile_tel_number"] : '' }}</td>
                    @endif

                </tr>
                @endif

                @endforeach
            @else
                <tr>
                    <td colspan="8" style="text-align: center;">No Data Found</td>
                </tr>
            @endif
        </tbody>
    </table>
@endif



    {{-- 12. NOTES  --}}
    @if (!empty($request->notes) && count($user->notes))
    <table>
        <h3>Notes</h3>
        <thead>
            <tr class="table_head_row">
                <th class="index_col"></th>
                @if (!empty($request->notes_title))
                    <th>Title</th>
                @endif
                @if (!empty($request->notes_description))
                    <th>Description</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @if (1)
                @foreach ($user->notes as $index => $note)
                    <tr class="table_row">
                        <td class="index_col">{{ $index + 1 }}</td>
                        @if (!empty($request->notes_title))
                            <td>{{ $note->title }}</td>
                        @endif
                        @if (!empty($request->notes_description))
                            <td>{{ $note->description }}</td>
                        @endif
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="8" style="text-align: center;">No Data Found</td>
                </tr>
            @endif
        </tbody>
    </table>
@endif



    {{-- 13. BANK DETAILS  --}}
    @if (!empty($request->bank_details) && !empty($user->bank_details_name))
    <table>
        <h3>Bank Details</h3>
        <thead>
            <tr class="table_head_row">
                <th class="index_col"></th>
                @if (!empty($request->bank_details_name))
                    <th>Bank Name</th>
                @endif
                @if (!empty($request->bank_details_sort_code))
                    <th>Sort Code</th>
                @endif
                @if (!empty($request->bank_details_account_name))
                    <th>Account Name</th>
                @endif
                @if (!empty($request->bank_details_account_number))
                    <th>Account Number</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @if (1)
                <tr class="table_row">
                    <td class="index_col">1</td>
                    @if (!empty($request->bank_details_name))
                        <td>{{ $user->bank->name }}</td>
                    @endif
                    @if (!empty($request->bank_details_sort_code))
                        <td>{{ $user->sort_code }}</td>
                    @endif
                    @if (!empty($request->bank_details_account_name))
                        <td>{{ $user->account_name }}</td>
                    @endif
                    @if (!empty($request->bank_details_account_number))
                        <td>{{ $user->account_number }}</td>
                    @endif
                </tr>
            @else
                <tr>
                    <td colspan="8" style="text-align: center;">No Data Found</td>
                </tr>
            @endif
        </tbody>
    </table>
@endif



    {{-- 14. SOCIAL LINKS  --}}
    @if (!empty($request->social_links) && count($user->social_links))
    <table>
        <h3>Social Links</h3>
        <thead>
            <tr class="table_head_row">
                <th class="index_col"></th>
                @if (!empty($request->social_links_website))
                    <th>Website</th>
                @endif
                @if (!empty($request->social_links_url))
                    <th>URL</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @if (1)
                @foreach ($user->social_links as $index => $social_link)
                    <tr class="table_row">
                        <td class="index_col">{{ $index + 1 }}</td>
                        @if (!empty($request->social_links_website))
                            <td>{{ $social_link->social_site->name }}</td>
                        @endif
                        @if (!empty($request->social_links_url))
                            <td>{{ $social_link->profile_link }}</td>
                        @endif
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="8" style="text-align: center;">No Data Found</td>
                </tr>
            @endif
        </tbody>
    </table>
@endif



</body>

</html>
