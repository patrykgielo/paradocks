<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Congratulations! You earned a discount coupon</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
        }
        h1 {
            color: #2c3e50;
            font-size: 24px;
            margin-bottom: 20px;
        }
        .coupon-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            margin: 20px 0;
        }
        .coupon-code {
            font-size: 36px;
            font-weight: bold;
            letter-spacing: 3px;
            margin: 15px 0;
        }
        .coupon-value {
            font-size: 20px;
            margin: 10px 0;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #3490dc;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            font-size: 12px;
            color: #777;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Congratulations, {{ $customer->first_name }}!</h1>

        <p>Thank you for completing your <strong>{{ $appointment->service->name }}</strong> appointment on {{ $appointment->appointment_date->format('d.m.Y') }}.</p>

        <p>As a reward for your trust, we are pleased to offer you a special discount code for your next visit:</p>

        <div class="coupon-box">
            <div>Your discount code:</div>
            <div class="coupon-code">{{ $coupon->code }}</div>
            <div class="coupon-value">Discount: {{ $coupon->formatted_discount }}</div>
            <div style="margin-top: 15px;">Valid until: {{ $coupon->valid_until->format('d.m.Y') }}</div>
        </div>

        <p>Use this code when booking your next appointment to receive your discount. The code is valid until {{ $coupon->valid_until->format('d.m.Y') }}.</p>

        <p style="text-align: center;">
            <a href="{{ url('/booking') }}" class="button">Book Appointment</a>
        </p>

        <p>We look forward to seeing you again!</p>

        <p>
            Best regards,<br>
            {{ config('app.name') }} Team
        </p>

        <div class="footer">
            <p>This message was sent automatically. Please do not reply.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
