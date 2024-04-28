<!DOCTYPE html>
<html>
<head>
    <title>Order Shipped Notification</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&display=swap" rel="stylesheet">

    <style>
        /* Add your custom CSS styles here */
        body {
            font-family: 'Nunito', sans-serif;
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

        th,
        td {
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
        <h1>Hi {{$user->firstname." ".$user->lastname}},</h1>
        <p>Thank you for shopping on Sosmart!</p>
        <p>Your order {{$order->order_number}} has been delivered and is now available for collection!</p>
        <br>
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
                @foreach($orderedItems as $item)
                <tr>
                    <td style="padding:4px; font-size:14px"><img src="{{ $item->product_img1 }}" alt=""></td>
                    <td style="padding:4px; font-size:14px">{{ $item->product_name }}</td>
                    <td style="padding:4px; font-size:14px">{{ $item->quantity }}</td>
                    <td style="padding:4px; font-size:14px">${{ $item->price }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <p style="font-size: 12px">If you have any questions, please don't hesitate to contact us at <a href="mailto:{{$response_email}}">{{$response_email}}</a>.</p>

        <p>Thank you for doing business with us!</p>

        <p class="footer">Sincerely,<br>Sosmart Shopping</p>
    </div>
</body>
</html>
