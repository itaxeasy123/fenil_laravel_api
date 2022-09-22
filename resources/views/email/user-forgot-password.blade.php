<!DOCTYPE html>
<html>

<head>
    <title>{{ env('APP_NAME') }}</title>
</head>

<body>
    <h1>{{ $details['subject'] }}</h1>
    <p>We have received request for generating token to forgot password via your email. Please use below code to reset your password.</p><br>
    <p>Token : <b>{{ $details['token'] }}</b></p>

    <p><b>Note: </b>Your token will expire after 5 minute!</p>
    <p>Thank you</p>
</body>

</html>
