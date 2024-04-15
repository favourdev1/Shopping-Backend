<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Sosmart Shopping - Password Reset</title>
        <style>
            html, body {
                height: 100%;
                width:100%;
                margin: 0;
                padding: 0;
                font-family: 'Roboto', sans-serif;
                background-color: #f5f5f5;
            }

            .body-container {
                display: flex;
                align-items: center;
                justify-content: center;
                flex-direction: column;
                height: 100%;
                padding: 20px;
            }

            .container {
                background-color: #fff;
                padding: 20px;
                max-width: 600px;
                border-radius: 10px;
                display: flex;
                align-items: start;
                justify-content: start;
                flex-direction: column;
                text-align: left;
                box-shadow: 0px 0px 10px rgba(0,0,0,0.1);
            }

            h4 {
                font-size: 24px;
                color: #333;
                margin: 20px auto;
            }

            .thank-you {
                font-size: 16px;
                color: #666;
                margin-bottom: 30px;
            }

            .back-to-home {
                display: inline-block;
                padding: 10px 20px;
                background: linear-gradient(to right, #007bff, #0056b3);
                color: #fff;
                text-decoration: none;
                border-radius: 5px;
                transition: background-color 0.3s ease;
                margin: 20px 0;
            }

            img {
                margin: 0 auto;
                display: block;
                height: 40px;
            }

            @media (max-width: 600px) {
                .container {
                    padding: 10px;
                }

                h4 {
                    font-size: 20px;
                }

                .thank-you {
                    font-size: 14px;
                }
            }
        </style>
    </head>
    
<body>
    <div class="body-container">
        <img src="{{ asset('storage/assets/sosmart-logo.png') }}" alt="Subscribe">
        <h4>Reset Password</h4>
        <div class="container">
            <small>Dear user,</small>
            <p class="thank-you">You are receiving this email because we received a password reset request for your account.</p>
            <a href="{{ $resetUrl }}" class="back-to-home"> Reset your password here</a>
            <p class="thank-you">If you did not ask to reset your password, please ignore this email and nothing will change.</p>
            <small>Warm Regards,</small>
        </div>
    </div>
</body>
</html>