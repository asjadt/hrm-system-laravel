<!DOCTYPE html>
<html>
<head>
    <title>Attendance List</title>

    <!--ALL CUSTOM FUNCTIONS -->
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
            <td class="file_title">Attendance List </td>
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
        <table>
            <h3>Attendances</h3>
            <thead>
                <tr class="table_head_row">
                    <th class="index_col"></th>
                    @if (!empty($request->attendance_date) || 1)
                        <th>Date</th>
                    @endif
                    @if (!empty($request->attendance_start_time)|| 1)
                        <th>Start Time</th>
                    @endif
                    @if (!empty($request->attendance_end_time) || 1)
                        <th>End Time</th>
                    @endif
                    @if (!empty($request->attendance_break) || 1)
                        <th>Break (hour)</th>
                    @endif
                    @if (!empty($request->attendance_schedule) || 1)
                        <th>Schedule (hour)</th>
                    @endif
                    @if (!empty($request->attendance_overtime) || 1)
                        <th>Overtime (hour)</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @if (count($attendances))
                    @foreach ($attendances as $index => $attendance)
                        <tr class="table_row">
                            <td class="index_col">{{ $index + 1 }}</td>

                            @if (!empty($request->attendance_date) || 1)
                            <td>{{ format_date($attendance->in_date) }}</td>
                        @endif

                            @if (!empty($request->attendance_start_time) || 1)
                                <td>{{ $attendance->in_time }}</td>
                            @endif
                            @if (!empty($request->attendance_end_time) || 1)
                                <td>{{ $attendance->out_time }}</td>
                            @endif
                            @if (!empty($request->attendance_break) || 1)
                                <td>{{ $attendance->does_break_taken ? time_format($attendance->break_hours) : 0 }}</td>
                            @endif
                            @if (!empty($request->attendance_schedule) || 1)
                                <td>{{ time_format($attendance->capacity_hours) }}</td>
                            @endif
                            @if (!empty($request->attendance_overtime) || 1)
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
    </table>

</body>
</html>
