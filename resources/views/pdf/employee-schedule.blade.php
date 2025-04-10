<!DOCTYPE html>
<html>
<head>
    <title>Employee Schedules</title>

    <!--ALL CUSTOM FUNCTIONS -->
    @php

        function processString($inputString) {

            $withoutUnderscore = str_replace('_', '', $inputString);


            $finalString = explode('#', $withoutUnderscore)[0];

         
            $capitalizedString = ucwords($finalString);

            return $capitalizedString;
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
            <td class="file_title">Employee Schedules </td>
          </tr>
          <tr>
            <td>
                <span class="business_name">{{$business->name}}</span>
                <address class="business_address">{{$business->address_line_1}}</address>
            </td>

          </tr>
        </tbody>
    </table>


    @foreach($employees as $index=>$employee)
    <table>
        <thead>
            <tr class="table_head_row">
                <th></th>
                <th>Date</th>
                <th>Start At</th>
                <th>End At</th>
                <th>Capacity Hours </th>
                <th>Break Hours </th>
                <th>Break Type </th>
                <th>Is Weekend  </th>
            </tr>
        </thead>
        <tbody>
            @foreach($employee->schedule_data as $index=>$schedule_data)
                <tr class="table_row">
                    <td  style="padding:0px 10px">{{ $index+1 }}</td>

                    <td>

                        {{ format_date($schedule_data["date"]) }}

                    </td>
                    <td > {{ $schedule_data["start_at"] }} </td>
                    <td > {{ $schedule_data["end_at"] }} </td>
                    <td > {{ $schedule_data["capacity_hours"] }} </td>
                    <td > {{ $schedule_data["break_type"] }} </td>
                    <td > {{ $schedule_data["break_hours"] }} </td>
                    <td > {{ $schedule_data["is_weekend"] }} </td>

                </tr>
            @endforeach
        </tbody>
    </table>
    @endforeach


</body>
</html>
