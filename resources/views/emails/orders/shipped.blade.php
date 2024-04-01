<!DOCTYPE html>
<html>
<head>
    <title>Order Shipped Notification</title>
    <style>
        /* Add your custom CSS styles here */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #333333;
            font-size: 24px;
            margin-bottom: 20px;
        }
        p {
            color: #666666;
            font-size: 16px;
            line-height: 1.5;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #dddddd;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        a {
            color: #007bff;
            text-decoration: none;
        }
        .footer {
            margin-top: 20px;
            color: #999999;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
    <img src="">
        <h1>Hi Baby,</h1>
        <p>Thank you for shopping on Sosmart!</p>
        <p>Your order (ID: 1123456789) has been shipped!</p>
        <br>
        <p>It will be packed and shipped as soon as possible.You will receive a notification from us once the item (s) are available for collection</p>
        <p>Your order details are:</p>

        <table>
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orderItems as $item)
                    <tr>
                        <td>{{ $item->product_name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ $item->price }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <p>You can expect to receive your order within {{ $order->estimated_delivery_days }} business days.</p>
        <p>If you have any questions, please don't hesitate to contact us at <a href="mailto:your_email@example.com">your_email@example.com</a>.</p>

        <p>Thank you for your business!</p>

        <p class="footer">Sincerely,<br>Sosmart Shopping</p>
    </div>
</body>
</html>
