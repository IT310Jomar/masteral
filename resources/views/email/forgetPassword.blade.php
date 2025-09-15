<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            max-width: 600px;
            background-color: #ffffff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #007bff;
            margin-bottom: 30px;
        }

        p {
            margin-bottom: 20px;
            line-height: 1.6;
        }

        a {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
        }

        a:hover {
            background-color: #0056b3;
            color: #ffffff;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Password Reset</h2>
        <p>Hello {{$user->username}},</p>
        <p>Click the link below to reset your password:</p>
        <a href="{{ url('http://127.0.0.1:8000/reset-password') }}?token={{ $token }}" style="color:black">Reset Password</a>
        <p>If you didn't request a password reset, you can ignore this email.</p>
    </div>
</body>
</html>
