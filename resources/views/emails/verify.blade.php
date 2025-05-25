<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Your Email</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f4f4; font-family: 'Segoe UI', Arial, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);">
                    <tr>
                        <td align="center" style="background-color: #ff6600; padding: 25px;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 32px; font-weight: 700; letter-spacing: 2px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                                Sarea
                            </h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="color: #333333;">Welcome to Sarea 👋</h2>
                            <p style="font-size: 16px; color: #555555;">To finish setting up your account, please verify your email address using the verification code below:</p>
                            <div style="text-align: center; margin: 35px 0;">
                                <span style="display: inline-block; font-size: 32px; font-weight: bold; letter-spacing: 6px; background-color: #f1f1f1; padding: 20px 35px; border-radius: 10px; color: #007bff;">
                                    {{ $code }}
                                </span>
                            </div>
                            <p style="font-size: 14px; color: #999999;">Thanks,<br>The Sarea Team</p>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding: 20px; background-color: #f0f0f0; font-size: 12px; color: #888888;">
                            &copy; {{ date('Y') }} Sarea. All rights reserved.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
