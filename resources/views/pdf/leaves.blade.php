<!DOCTYPE html>
<html>
<head>
    <title>Leave Request List</title>


    @php

        function processString($inputString) {

            $withoutUnderscore = str_replace('_', '', $inputString);


            $finalString = explode('#', $withoutUnderscore)[0];


            $capitalizedString = ucwords($finalString);

            return $capitalizedString;
        }

        function time_format($breakHours) {
        if(!$breakHours){
            $breakHours = 0;
        }


$breakSeconds = round($breakHours * 3600);


$formattedBreakTime = gmdate("H:i:s", $breakSeconds);
return $formattedBreakTime;
    }
    function format_date($date) {
    return \Carbon\Carbon::parse($date)->format('d-m-Y');
}

    @endphp

    @php
        $business = auth()->user()->business;
    @endphp

@php
   $color  = env("FRONT_END_VERSION") == "red"?"#dc2b28" : "#335ff0";
@endphp
    <style>
        /* Add any additional styling for your PDF */
        body {
            font-family: Arial, sans-serif;
            margin:0;
            padding:0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size:10px;


        }
        .table_head_row{
            color:#fff;
            background-color:{{$color}};
            font-weight:600;
        }
        .table_head_row td{
            color:#fff;
        }
        .table_head_row th, tbody tr td {
            text-align: left;
            padding:10px 0px;
        }
        .table_row {
            background-color:#ffffff;
        }
        .table_row td{
            padding:10px 0px;
            border-bottom:0.2px solid #ddd;
        }

        .employee_index{

        }
        .employee{
            color:{{$color}};
            /*font-weight:600;*/
        }
        .employee_name{

        }
        .role{

        }
    .logo{
        width:75px;
        height:75px;
    }
    .file_title{
        font-size:1.3rem;
        font-weight:bold;
        text-align:right;
    }
    .business_name{
        font-size:1.2rem;
        font-weight:bold;
        display:block;
    }
    .business_address{

    }
    </style>

</head>
<body>

    <table style="margin-top:-30px">
       <tbody>
          <tr>
            @if ($business->logo)
            <td rowspan="2">
                <img class="logo" src="{{public_path($business->logo)}}" >
            </td>
            @endif
            <td></td>
          </tr>
          <tr>
            <td class="file_title">Leave Request List </td>
          </tr>
          <tr>
            <td>
                <span class="business_name">{{$business->name}}</span>
                <address class="business_address">{{$business->address_line_1}}</address>
            </td>

          </tr>
        </tbody>
    </table>


    <table>
        <h3>Leaves</h3>
        <thead>
            <tr class="table_head_row">
                <th class="index_col"></th>
                @if (!empty($request->leave_date_time) || 1)
                    <th>Date & Time</th>
                @endif
                @if (!empty($request->leave_type) || 1)
                    <th>Type</th>
                @endif
                @if (!empty($request->leave_duration) || 1)
                    <th>Duration</th>
                @endif
                @if (!empty($request->total_leave_hours) || 1)
                    <th>Total Leave (hours)</th>
                @endif
                {{-- <th>Attachment</th> --}}
            </tr>
        </thead>
        <tbody>
            @if (count($leaves))
                @foreach ($leaves as $index => $leave)
                    <tr class="table_row">
                        <td class="index_col">{{ $index + 1 }}</td>
                        @if (!empty($request->leave_date_time) || 1)
                            <td>{{ format_date($leave->start_date) }} - {{ format_date($leave->end_date) }}</td>
                        @endif
                        @if (!empty($request->leave_type) || 1)
                            <td>{{ $leave->leave_type ? $leave->leave_type->name : "" }}</td>
                        @endif
                        @if (!empty($request->leave_duration) || 1)
                            <td>{{ $leave->leave_duration }}</td>
                        @endif
                        @if (!empty($request->total_leave_hours) || 1)
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

</body>
</html>
