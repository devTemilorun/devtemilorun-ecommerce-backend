<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dispute Created</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: #DC2626; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1>Payment Dispute Created</h1>
    </div>

    <div style="background: #f9fafb; padding: 30px; border-radius: 0 0 8px 8px;">
        <p>A new payment dispute has been created that requires your attention.</p>

        <hr style="border: 1px solid #e5e7eb; margin: 20px 0;">

        <h3>Dispute Details</h3>
        <table style="width: 100%;">
            <tr>
                <td style="padding: 8px 0;"><strong>Transaction Reference:</strong></td>
                <td style="padding: 8px 0; text-align: right;">{{ $transaction['reference'] ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0;"><strong>Amount:</strong></td>
                <td style="padding: 8px 0; text-align: right;">{{ $currency }} {{ number_format($amount, 2) }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0;"><strong>Reason:</strong></td>
                <td style="padding: 8px 0; text-align: right;">{{ $reason }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0;"><strong>Status:</strong></td>
                <td style="padding: 8px 0; text-align: right;">
                    <span style="display: inline-block; padding: 4px 12px; background: #DC2626; color: white; border-radius: 4px;">
                        Pending Review
                    </span>
                </td>
            </tr>
        </table>

        @if(!empty($disputeData['customer']))
        <hr style="border: 1px solid #e5e7eb; margin: 20px 0;">
        <h3>Customer Information</h3>
        <p>
            <strong>Name:</strong> {{ $disputeData['customer']['name'] ?? 'N/A' }}<br>
            <strong>Email:</strong> {{ $disputeData['customer']['email'] ?? 'N/A' }}<br>
            <strong>Phone:</strong> {{ $disputeData['customer']['phone'] ?? 'N/A' }}
        </p>
        @endif

        <hr style="border: 1px solid #e5e7eb; margin: 20px 0;">

        <div style="text-align: center; margin-top: 20px;">
            <a href="{{ config('app.frontend_url', 'http://localhost:3000') }}/admin/orders" 
               style="display: inline-block; background: #DC2626; color: white; padding: 12px 30px; text-decoration: none; border-radius: 6px;">
                View Dispute in Admin
            </a>
        </div>

        <p style="margin-top: 30px; color: #6b7280; font-size: 14px;">
            Please review this dispute and take appropriate action.
            <br><br>
            Thanks,<br>
            <strong>{{ $appName }}</strong>
        </p>
    </div>
</body>
</html>