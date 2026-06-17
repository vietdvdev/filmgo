<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Khôi Phục Mật Khẩu - FilmGo</title>
    <style>
        body {
            background-color: #0F0F0F;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #FFFFFF;
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: none;
            -ms-text-size-adjust: none;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #1A1A1A;
            border: 1px solid #2A2A2A;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #E50914;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo-text {
            font-size: 28px;
            font-weight: 900;
            letter-spacing: 2px;
            color: #FFFFFF;
            text-decoration: none;
            text-transform: uppercase;
        }
        .brand-primary {
            color: #E50914;
        }
        .content {
            font-size: 15px;
            line-height: 1.6;
            color: #CCCCCC;
            margin-bottom: 30px;
        }
        .greeting {
            font-size: 18px;
            font-weight: bold;
            color: #FFFFFF;
            margin-bottom: 15px;
        }
        .btn-wrapper {
            text-align: center;
            margin: 35px 0;
        }
        .btn {
            background-color: #E50914;
            color: #FFFFFF !important;
            padding: 14px 30px;
            font-size: 14px;
            font-weight: bold;
            text-decoration: none;
            letter-spacing: 1px;
            text-transform: uppercase;
            display: inline-block;
            transition: background-color 0.3s;
        }
        .footer {
            font-size: 12px;
            color: #666666;
            text-align: center;
            border-top: 1px solid #2A2A2A;
            padding-top: 20px;
            margin-top: 40px;
            line-height: 1.5;
        }
        .link-alternative {
            word-break: break-all;
            color: #E50914;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <span class="logo-text">FILM<span class="brand-primary">GO</span></span>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">Xin chào {{ $name }},</div>
            <p>Chúng tôi nhận được yêu cầu thiết lập lại mật khẩu cho tài khoản FilmGo của bạn. Vui lòng bấm vào nút bên dưới để tiến hành khôi phục:</p>
            
            <div class="btn-wrapper">
                <a href="{{ $url }}" class="btn">Khôi Phục Mật Khẩu</a>
            </div>

            <p>Liên kết khôi phục mật khẩu này **chỉ có hiệu lực trong vòng 60 phút**. Nếu bạn không thực hiện yêu cầu này, bạn có thể bỏ qua email này, mật khẩu hiện tại của bạn vẫn được giữ nguyên.</p>
            
            <p>Nếu gặp sự cố khi bấm nút, bạn có thể sao chép liên kết dưới đây và dán vào trình duyệt web:</p>
            <p><a href="{{ $url }}" class="link-alternative">{{ $url }}</a></p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Đây là email tự động từ hệ thống FilmGo. Vui lòng không phản hồi email này.</p>
            <p>&copy; {{ date('Y') }} FilmGo Project. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
