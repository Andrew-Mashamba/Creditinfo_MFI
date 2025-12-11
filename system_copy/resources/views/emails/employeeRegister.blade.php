<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Template</title>
    <style nonce="{{ csp_nonce() }}">
        /* Basic CSS reset */
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            line-height: 1.6;
            background-color: #f4f4f4;
        }
        /* Wrapper for the email content */
        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* Header styling */
        .header {
            text-align: center;
            padding-bottom: 20px;
        }

        /* Content area */
        .content {
            padding: 20px 0;
        }

        /* Footer styling */
        .footer {
            text-align: center;
            color: #777;
            font-size: 14px;
            margin-top: 20px;
        }

        /* Button style */
        .btn {
            display: inline-block;
            padding: 10px 20px;
            text-decoration: none;
            background-color: #e82845;
            color: #fff;
            border-radius: 4px;
        }
    </style>
</head>

<body>
<div class="email-wrapper">
    <div class="header">
        Dear {{$name}},
    </div>
    <div class="content">

        <p> We are delighted to inform you that your registration for access to our system has been successfully completed. Welcome aboard.
        </p>

        <p>
            To log in and begin exploring our system's features and functionalities, please use the email address you provided during registration and the password you set up.
        </p>

        <p>
            <div> Username or email : {{$email}}</div>
            <div> Password : {{$password}}</div>
        </p>

        <p>
         To login    <a href="{{$link}}" class="btn">Click Here! </a>
        </p>




    </div>
    <div class="footer">

        <p>
            Thank you for trusting to our microfinance system. <br>
            Best regards,<br>  </p>

        <div class="cXedhc">Phone No: {{$phone_number}}</div>
        <div class="cXedhc">email: {{$officer_email}}</div>

        <p>Powered by, <a href="https://isalegroup.co.tz/">isalegroup.co.tz</a>.</p>
    </div>



</div>
</div>
</body>
</html>
