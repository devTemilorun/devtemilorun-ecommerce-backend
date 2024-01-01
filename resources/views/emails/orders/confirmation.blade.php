<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: #4F46E5; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1>Order Confirmed! </h1>
    </div>

    <div style="background: #f9fafb; padding: 30px; border-radius: 0 0 8px 8px;">
        <h2>Hi {{ $order->user->name }},</h2>

        <p>Thank you for your order. We've received it and it's being processed.</p>

        <hr style="border: 1px solid #e5e7eb; margin: 20px 0;">

        <h3>Order Details</h3>
        <p><strong>Order Number:</strong> {{ $order->order_number }}</p>
        <p><strong>Date:</strong> {{ $order->created_at->format('F j, Y') }}</p>
        <p><strong>Payment Method:</strong> {{ ucfirst($order->payment_method) }}</p>

        <hr style="border: 1px solid #e5e7eb; margin: 20px 0;">

        <h3>Items Ordered</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #e5e7eb;">
                    <th style="padding: 8px; text-align: left;">Product</th>
                    <th style="padding: 8px; text-align: center;">Qty</th>
                    <th style="padding: 8px; text-align: right;">Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                <tr>
                    <td style="padding: 8px; border-bottom: 1px solid #e5e7eb;">{{ $item->product_name }}</td>
                    <td style="padding: 8px; text-align: center; border-bottom: 1px solid #e5e7eb;">{{ $item->quantity }}</td>
                    <td style="padding: 8px; text-align: right; border-bottom: 1px solid #e5e7eb;">₦{{ number_format($item->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <hr style="border: 1px solid #e5e7eb; margin: 20px 0;">

        <h3>Order Summary</h3>
        <table style="width: 100%;">
            <tr>
                <td style="padding: 4px 0;">Subtotal</td>
                <td style="padding: 4px 0; text-align: right;">₦{{ number_format($order->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td style="padding: 4px 0;">Shipping</td>
                <td style="padding: 4px 0; text-align: right;">
                    {{ $order->shipping_cost > 0 ? '₦' . number_format($order->shipping_cost, 2) : 'Free' }}
                </td>
            </tr>
            <tr>
                <td style="padding: 4px 0;">Tax</td>
                <td style="padding: 4px 0; text-align: right;">₦{{ number_format($order->tax, 2) }}</td>
            </tr>
            <tr style="font-size: 18px; font-weight: bold;">
                <td style="padding: 8px 0;">Total</td>
                <td style="padding: 8px 0; text-align: right;">₦{{ number_format($order->total, 2) }}</td>
            </tr>
        </table>

        <hr style="border: 1px solid #e5e7eb; margin: 20px 0;">

        <h3>Shipping Address</h3>
        @php
            $address = $order->shipping_address;
        @endphp
        <p>
            {{ $address['first_name'] ?? '' }} {{ $address['last_name'] ?? '' }}<br>
            {{ $address['address_line1'] ?? '' }}<br>
            {{ $address['city'] ?? '' }}, {{ $address['state'] ?? '' }} {{ $address['postal_code'] ?? '' }}<br>
            {{ $address['country'] ?? '' }}
        </p>

        <p style="margin-top: 20px;">We'll send you another email when your order ships.</p>

        <p style="margin-top: 30px; color: #6b7280; font-size: 14px;">
            Thanks,<br>
            <strong>{{ config('app.name') }}</strong>
        </p>
    </div>
</body>
</html>