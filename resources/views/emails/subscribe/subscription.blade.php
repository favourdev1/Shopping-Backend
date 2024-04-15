<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You for Subscribing!</title>
    <style>
        /* Reset some default styles */
        html {
            height: 100%;
            width:100%;
        }

*{
    text-align:center!important;
}
        /* Body styles */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #E1E6E9 !important;
            margin: 0;
            padding: 0;
            height: 100%;
        }

        /* Body container styles */
        .body-container {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            height: 100%;
        }

        /* Container styles */
        .container {
            background-color: #f5f5f5;
            padding: 20px;
            max-width: 600px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            text-align: center;
        }

        /* Heading styles */
        h1 {
            font-size: 32px;
            color: #333;
            margin: 20px auto;
        }

        /* Thank you message styles */
        .thank-you {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
        }

        /* Contact email styles */
        .contact-email {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .contact-email:hover {
            color: #0056b3;
        }

        /* Footer styles */
        .footer {
            font-size: 12px;
            color: #666;
            margin-top: 20px;
        }

        /* Back to home button styles */
        .back-to-home {
            display: inline-block;
            padding: 8px 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .back-to-home:hover {
            background-color: #0056b3;
        }

        /* Image styles */
        img {
            margin: 0 auto;
            display: block;
            height: 40px;
        }

        /* Media query for smaller screens */
        @media (max-width: 600px) {
            .container {
                padding: 10px;
            }

            h1 {
                font-size: 28px;
            }

            .thank-you {
                font-size: 14px;
            }

            .footer {
                font-size: 10px;
            }
        }
    </style>
</head>

<body>
    <div class="body-container">
        <img src="{{ asset('storage/assets/sosmart-logo.png') }}" alt="Subscribe">
        <h4>Thank You for Subscribing!</h4>
        <div class="container">
            <img src="{{ asset('storage/assets/email-50.png') }}" alt="Subscribe">
            <p class="thank-you">We're delighted to welcome you aboard!<br>
                By subscribing, you've taken the first step in staying updated with all the latest news, offers, and
                exciting developments from our website. Whether it's exclusive discounts, informative content, or new
                product launches, we're committed to delivering valuable updates straight to your inbox.</p>
            <p>If you have any questions or need further assistance, please don't hesitate to contact us at <a href="mailto:your@email.com"
                    class="contact-email">{{env('MAIL_FROM_ADDRESS')}}</a>.</p>
            <p class="footer">Sincerely,<br>Sosmart Shopping</p>
            <a href="https://sosmartshopping.com" class="back-to-home">Go Shopping</a>
        </div>
    </div>
</body>

</html>
