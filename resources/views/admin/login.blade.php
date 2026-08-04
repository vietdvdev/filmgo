<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập hệ thống Admin - FilmGo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            top: -100px;
            left: -100px;
            animation: float 6s ease-in-out infinite;
        }

        body::after {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
            bottom: -50px;
            right: -50px;
            animation: float 8s ease-in-out infinite reverse;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(20px);
            }
        }

        .login-wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 450px;
            padding: 20px;
        }

        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }

        .login-header .brand-icon {
            font-size: 48px;
            margin-bottom: 12px;
            display: inline-block;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }

        .login-header h1 {
            font-size: 32px;
            font-weight: 700;
            margin: 0;
            letter-spacing: 1px;
        }

        .login-header p {
            font-size: 14px;
            margin-top: 8px;
            opacity: 0.9;
            margin: 0;
        }

        .login-body {
            padding: 40px 30px;
        }

        .alert-custom {
            background: #fee;
            border: 1px solid #fcc;
            color: #c33;
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 24px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: shake 0.5s;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .form-group-custom {
            margin-bottom: 20px;
        }

        .form-group-custom label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .form-group-custom label i {
            color: #667eea;
            font-size: 18px;
        }

        .form-group-custom input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e8e8f0;
            border-radius: 12px;
            font-size: 14px;
            transition: all 0.3s ease;
            font-family: 'Segoe UI', sans-serif;
        }

        .form-group-custom input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            background: #f9f9ff;
        }

        .form-group-custom input::placeholder {
            color: #999;
        }

        .btn-login {
            width: 100%;
            padding: 14px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            margin-top: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-login i {
            font-size: 18px;
        }

        .login-footer {
            text-align: center;
            padding: 0 30px 30px;
            font-size: 12px;
            color: #999;
        }

        .security-info {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            margin-top: 16px;
        }

        .security-info i {
            color: #667eea;
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    <div class="login-card">
        <div class="login-header">
            <div class="brand-icon">
                <i class="bi bi-film"></i>
            </div>
            <h1>FILMGO</h1>
            <p>Quản Lý Hệ Thống Admin</p>
        </div>

        <div class="login-body">
            @if ($errors->has('login_error'))
                <div class="alert-custom">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    {{ $errors->first('login_error') }}
                </div>
            @endif

            <form action="{{ route('admin.login') }}" method="POST">
                @csrf

                <div class="form-group-custom">
                    <label for="email">
                        <i class="bi bi-envelope"></i>
                        Email hoặc Tài khoản
                    </label>
                    <input 
                        type="email" 
                        name="email" 
                        id="email" 
                        class="form-control" 
                        placeholder="Nhập email của bạn..." 
                        value="{{ old('email') }}" 
                        required
                        autocomplete="email"
                    >
                </div>

                <div class="form-group-custom">
                    <label for="password">
                        <i class="bi bi-lock"></i>
                        Mật khẩu
                    </label>
                    <input 
                        type="password" 
                        name="password" 
                        id="password" 
                        class="form-control" 
                        placeholder="Nhập mật khẩu..." 
                        required
                        autocomplete="current-password"
                    >
                </div>

                <button type="submit" class="btn-login">
                    <i class="bi bi-box-arrow-in-right"></i>
                    Đăng Nhập
                </button>
            </form>

            <div class="security-info">
                <i class="bi bi-shield-check"></i>
                <span>Kết nối an toàn được mã hóa SSL</span>
            </div>
        </div>

        <div class="login-footer">
            <p>&copy; 2024 FilmGo. Bảo vệ bởi quy trình xác thực an toàn.</p>
        </div>
    </div>
</div>

</body>
</html>