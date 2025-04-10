<!DOCTYPE html>
<html>
<head>
    <title>Job History List</title>

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
            <td class="file_title">Job History List </td>
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
                @if (count($user_job_histories))
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


</body>
</html>
