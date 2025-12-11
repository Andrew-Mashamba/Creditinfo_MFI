<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email </title>
    <style nonce="{{ csp_nonce() }}">
        /* Reset some default styles for cross-client compatibility */
        body, table, td, th {
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }

        /* Set a maximum width for the email content */
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
        }

        /* Style the header and logo */
        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo {
            width: 100px;
            height: auto;
        }

        /* Style the main content of the email */
        .content {
            background-color: #f9f9f9;
            padding: 20px;
        }

        /* Style the call-to-action button */
        .cta-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #012042;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>
<!-- Email container with a maximum width -->
<div class="email-container">
    <!-- Header section -->
    <div class="header">

    </div>

    <!-- Main content section -->
    <div class="content">
        <h1>Hello </h1> company name or institution
        <p> You have successfully request for saccoss registration you  will receive feedback  within 24 hours</p>

        <main>
            {{--            {{$data['body'] }}--}}
        </main>
        <p>
            <a class="cta-button" href="#www.nbc.co.tz">NBC website</a> to visit our website.
        </p>

        <p>Thank you for using our service.</p>
    </div>
</div>
</body>
</html>
