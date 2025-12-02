{{-- resources/views/emails/otp.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset OTP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            background-color: #f4f4f4;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
        }
        .otp-code {
            background-color: #007bff;
            color: white;
            font-size: 32px;
            font-weight: bold;
            padding: 20px;
            border-radius: 5px;
            letter-spacing: 5px;
            margin: 20px 0;
        }
        .warning {
            color: #dc3545;
            font-size: 14px;
            margin-top: 20px;
        }
        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Password Reset Request</h2>
        <p>Hello {{ $userName }},</p>
        <p>You have requested to reset your password. Use the OTP code below:</p>
        
        <div class="otp-code">
            {{ $otp }}
        </div>
        
        <p>This OTP will expire in <strong>10 minutes</strong>.</p>
        
        <div class="warning">
            <strong>⚠️ Warning:</strong> If you did not request this password reset, please ignore this email.
        </div>
        
        <div class="footer">
            <p>This is an automated email. Please do not reply.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>