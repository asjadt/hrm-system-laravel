<!DOCTYPE html>
<html>
<head>
    <title>Payrun List</title>


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
            <td class="file_title">Payrun List </td>
          </tr>
          <tr>
            <td>
                <span class="business_name">{{$business->name}}</span>

            </td>


          </tr>
          <tr>
            <td>
                <address class="business_address">{{$business->address_line_1}}</address>
            </td>
          </tr>
        </tbody>
    </table>


    <table>
        <thead>
            <tr class="table_head_row">
                <th></th>
                <th>Period Type</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Generating Type</th>
                <th>Consider Type</th>
                <th>Consider Overtime</th>








            </tr>
        </thead>
        <tbody>
            @foreach($payruns as $index=>$payrun)
                <tr class="table_row">
                    <td class="employee_index" style="padding:0px 10px">{{ $index+1 }}</td>
                    <td class="period_type">
                        {{ $payrun->period_type }}
                    </td>
                    <td class="start_date">
                        {{ format_date($payrun->start_date) }}
                    </td>
                    <td class="end_date">
                        {{ format_date($payrun->end_date) }}
                    </td>
                    <td class="generating_type">
                        {{ $payrun->generating_type }}
                    </td>
                    <td class="consider_type">
                        {{ $payrun->consider_type }}
                    </td>
                    <td class="consider_overtime">
                        {{ $payrun->consider_overtime }}
                    </td>

                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>

