<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: #4F46E5;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background: #f9fafb;
            padding: 30px;
            border-radius: 0 0 8px 8px;
        }
        .button {
            display: inline-block;
            background: #4F46E5;
            color: white !important;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
            font-weight: bold;
        }
        .button:hover {
            background: #4338CA;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 14px;
            color: #6b7280;
            text-align: center;
        }
        .code {
            background: #e5e7eb;
            padding: 10px;
            border-radius: 4px;
            font-family: monospace;
            word-break: break-all;
            font-size: 14px;
            margin: 10px 0;
        }
        .info-box {
            background: #f0f4ff;
            border-left: 4px solid #4F46E5;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Verify Your Email</h1>
    </div>
    
    <div class="content">
        <h2>Hello {{ $userName }}!</h2>
        
        <p>Thank you for registering with <strong>{{ $appName }}</strong>.</p>
        
        <div class="info-box">
            <p><strong> Your Email:</strong> {{ $user->email }}</p>
        </div>
        
        <p>Please click the button below to verify your email address and activate your account:</p>
        
        <div style="text-align: center;">
            <a href="{{ $verificationUrl }}" class="button">Verify Email Address</a>
        </div>
        
        <p>Or copy and paste this link into your browser:</p>
        <div class="code">{{ $verificationUrl }}</div>
        
        <p>This verification link will expire in 24 hours.</p>
        
        <p>If you did not create an account with {{ $appName }}, please ignore this email.</p>
        
        <div class="footer">
            <p>Thanks,<br>{{ $appName }} Team</p>
            <p style="font-size: 12px; color: #9ca3af;">
                This is an automated message, please do not reply to this email.
            </p>
        </div>
    </div>
</body>
</html>