<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Status Update</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: #4F46E5; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1>Order Status Update</h1>
    </div>

    <div style="background: #f9fafb; padding: 30px; border-radius: 0 0 8px 8px;">
        <h2>Hi {{ $order->user->name }},</h2>

        <p>Your order <strong>#{{ $order->order_number }}</strong> status has been updated.</p>

        <hr style="border: 1px solid #e5e7eb; margin: 20px 0;">

        <h3>Status Change</h3>
        <p><strong>New Status:</strong> <span style="display: inline-block; padding: 4px 12px; background: #4F46E5; color: white; border-radius: 4px;">{{ $newStatusLabel }}</span></p>

        @if($order->status === 'shipped')
        <div style="background: #e0f2fe; padding: 15px; border-radius: 6px; margin: 15px 0;">
            <h3 style="margin: 0;">Your Order Has Been Shipped! </h3>
            <p style="margin: 5px 0 0 0;">Your order is on its way!</p>
            @if($order->tracking_number)
            <p style="margin: 10px 0 0 0;"><strong>Tracking Number:</strong> {{ $order->tracking_number }}</p>
            @endif
        </div>
        @elseif($order->status === 'delivered')
        <div style="background: #dcfce7; padding: 15px; border-radius: 6px; margin: 15px 0;">
            <h3 style="margin: 0;">Your Order Has Been Delivered! </h3>
            <p style="margin: 5px 0 0 0;">Great news! Your order has been delivered.</p>
        </div>
        @elseif($order->status === 'cancelled')
        <div style="background: #fee2e2; padding: 15px; border-radius: 6px; margin: 15px 0;">
            <h3 style="margin: 0;">Your Order Has Been Cancelled </h3>
            <p style="margin: 5px 0 0 0;">If you have any questions, please contact our support team.</p>
        </div>
        @endif

        <hr style="border: 1px solid #e5e7eb; margin: 20px 0;">

        <h3>Order Summary</h3>
        <table style="width: 100%;">
            <tr>
                <td style="padding: 4px 0;">Order Number</td>
                <td style="padding: 4px 0; text-align: right;">#{{ $order->order_number }}</td>
            </tr>
            <tr>
                <td style="padding: 4px 0;">Total</td>
                <td style="padding: 4px 0; text-align: right;">₦{{ number_format($order->total, 2) }}</td>
            </tr>
            <tr>
                <td style="padding: 4px 0;">Status</td>
                <td style="padding: 4px 0; text-align: right;">{{ $newStatusLabel }}</td>
            </tr>
        </table>

        <div style="text-align: center; margin-top: 30px;">
            <a href="{{ config('app.frontend_url', 'http://localhost:3000') }}/orders/{{ $order->id }}" 
               style="display: inline-block; background: #4F46E5; color: white; padding: 12px 30px; text-decoration: none; border-radius: 6px;">
                View Your Order
            </a>
        </div>

        <p style="margin-top: 30px; color: #6b7280; font-size: 14px;">
            Thanks,<br>
            <strong>{{ config('app.name') }}</strong>
        </p>
    </div>
</body>
</html>