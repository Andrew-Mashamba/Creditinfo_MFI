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

       <p> I hope this message finds you well.
        I am writing to provide you with comprehensive information regarding
        your recent loan inquiry/application with our financial institution.
        :
        <p> {{$loan_progress}} </p>

        </p>





    </div>
    <div class="footer">

        <p>
            Thank you for trusting to our microfinance system. <br>
            Best regards,<br>  </p>

        <div class="cXedhc">contact person: {{$officer_phone_number}}</div>

        <p>Powered by, <a href="https://isalegroup.co.tz/">isalegroup.co.tz</a>.</p>
        </div>



</div>
</div>
</body>
</html>
