<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>User Account Blocked Notification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
            border: 1px solid #dddddd;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            text-align: center;
            border-bottom: 1px solid #f5c6cb;
        }
        .content {
            padding: 20px;
            line-height: 1.6;
        }
        .footer {
            margin-top: 20px;
            padding: 10px;
            text-align: center;
            background-color: #f8f9fa;
            border-top: 1px solid #e9ecef;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            margin-top: 20px;
            font-size: 16px;
            color: #ffffff;
            background-color: #007bff;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>User Account Blocked Notification</h1>
        </div>
        <div class="content">
            <p>Hello Admin,</p>
            <p>We would like to inform you that the account of <strong>{{ $user->name }}</strong> (Email: <strong>{{ $user->email }}</strong>) has been temporarily blocked due to sending an unusually high number of emails. This action has been taken to prevent potential spam activities and to ensure the safety and security of our platform.</p>
            <p>Below are the details of the user:</p>
            <ul>
                <li><strong>Name:</strong> {{ $user->name }}</li>
                <li><strong>Email:</strong> {{ $user->email }}</li>
                <li><strong>Account ID:</strong> {{ $user->id }}</li>


            @if(!empty($user->business_id))
                <li><strong>Business ID:</strong> {{ $user->business_id }}</li>
                @if($user->business)
                    <li><strong>Business Name:</strong> {{ $user->business->name }}</li>
                @else
                    <li><strong>Business Name:</strong> Not available</li>
                @endif
            @endif


                <li><strong>Blocked at:</strong> {{ now()->toDateTimeString() }}</li>
            </ul>
            <p>Please review the account activity and take appropriate action if necessary.</p>
            <p>Sincerely,</p>

        </div>

    </div>
</body>
</html>
