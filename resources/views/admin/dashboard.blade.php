<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ thống Quản trị - FilmGo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-attachment: fixed;
        }

        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #0f172a 0%, #1e293b 50%, #334155 100%);
            color: #fff;
            width: 280px;
            position: fixed;
            left: 0;
            top: 0;
            border-right: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.3);
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 30px 20px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }

        .sidebar-header .brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 5px;
        }

        .sidebar-header .brand-icon {
            font-size: 32px;
            color: #667eea;
        }

        .sidebar-header h4 {
            font-size: 24px;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 0;
        }

        .sidebar-header p {
            font-size: 12px;
            color: #cbd5e1;
            margin: 5px 0 0 0;
        }

        .sidebar-menu {
            padding: 25px 0;
            flex: 1;
        }

        .nav-link {
            color: #cbd5e1;
            font-weight: 500;
            padding: 14px 20px;
            margin: 5px 10px;
            border-radius: 10px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 12px;
            position: relative;
        }

        .nav-link:hover {
            background-color: rgba(102, 126, 234, 0.15);
            color: #fff;
            transform: translateX(5px);
        }

        .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .nav-link i {
            font-size: 18px;
            width: 20px;
        }

        .main-content {
            margin-left: 280px;
            padding: 40px 30px;
            min-height: 100vh;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        .top-bar h1 {
            font-size: 32px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }

        .user-greeting {
            background: white;
            padding: 12px 24px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
            color: #333;
        }

        .user-greeting i {
            font-size: 24px;
            color: #667eea;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border: none;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--card-gradient-1), var(--card-gradient-2));
        }

        .stat-card.blue {
            --card-gradient-1: #667eea;
            --card-gradient-2: #764ba2;
        }

        .stat-card.green {
            --card-gradient-1: #10b981;
            --card-gradient-2: #059669;
        }

        .stat-card.orange {
            --card-gradient-1: #f59e0b;
            --card-gradient-2: #d97706;
        }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.15);
        }

        .stat-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .stat-info h6 {
            color: #999;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 12px;
        }

        .stat-info h3 {
            font-size: 32px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }

        .stat-icon {
            font-size: 48px;
            opacity: 0.2;
        }

        .stat-card.blue .stat-icon {
            color: #667eea;
        }

        .stat-card.green .stat-icon {
            color: #10b981;
        }

        .stat-card.orange .stat-icon {
            color: #f59e0b;
        }

        .recent-bookings {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        }

        .recent-bookings h5 {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .recent-bookings h5 i {
            color: #667eea;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background: #f8f9fa;
            color: #666;
            font-weight: 600;
            border: none;
            padding: 16px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table tbody td {
            padding: 16px;
            border-bottom: 1px solid #f0f0f0;
            color: #333;
            font-weight: 500;
        }

        .table tbody tr:hover {
            background-color: #f8f9ff;
        }

        .badge {
            padding: 8px 12px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .ticket-id {
            color: #667eea;
            font-weight: 700;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }

            .main-content {
                margin-left: 200px;
                padding: 20px;
            }

            .top-bar {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }

            .user-greeting {
                width: 100%;
                justify-content: flex-start;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<div class="d-flex">
    <div class="sidebar d-flex flex-column">
        <div class="sidebar-header">
            <div class="brand">
                <i class="bi bi-film brand-icon"></i>
            </div>
            <h4>FILMGO</h4>
            <p>Quản Trị Admin</p>
        </div>

        <div class="sidebar-menu">
            <ul class="nav nav-pills flex-column gap-2 ps-0">
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link active">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.movies.index') }}" class="nav-link">
                        <i class="bi bi-film"></i>
                        <span>Quản lý phim</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.genres.index') }}" class="nav-link">
                        <i class="bi bi-tags"></i>
                        <span>Quản lý thể loại</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="bi bi-ticket-perforated"></i>
                        <span>Quản lý vé đặt</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="bi bi-people"></i>
                        <span>Quản lý thành viên</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="bi bi-building"></i>
                        <span>Quản lý rạp</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="bi bi-bar-chart"></i>
                        <span>Báo cáo thống kê</span>
                    </a>
                </li>
            </ul>
        </div>

        </div>

    <div class="main-content flex-grow-1">
        <div class="top-bar">
            <h1>Tổng Quan Hệ Thống</h1>
            <div style="display: flex; gap: 15px; align-items: center;">
                <div class="user-greeting">
                    <i class="bi bi-person-circle"></i>
                    <span>Xin chào, Admin!</span>
                </div>
                <form action="{{ route('admin.logout') }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" style="background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%); border: none; color: white; padding: 10px 20px; border-radius: 10px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 15px rgba(244, 63, 94, 0.3); transition: all 0.3s ease;">
                        <i class="bi bi-box-arrow-right"></i>
                        Đăng Xuất
                    </button>
                </form>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card blue">
                <div class="stat-content">
                    <div class="stat-info">
                        <h6>Tổng Số Phim</h6>
                        <h3>124</h3>
                    </div>
                    <i class="bi bi-film stat-icon"></i>
                </div>
            </div>

            <div class="stat-card green">
                <div class="stat-content">
                    <div class="stat-info">
                        <h6>Vé Đã Bán</h6>
                        <h3>1,245</h3>
                    </div>
                    <i class="bi bi-ticket-perforated stat-icon"></i>
                </div>
            </div>

            <div class="stat-card orange">
                <div class="stat-content">
                    <div class="stat-info">
                        <h6>Doanh Thu Tháng</h6>
                        <h3>84.5M đ</h3>
                    </div>
                    <i class="bi bi-currency-dollar stat-icon"></i>
                </div>
            </div>
        </div>

        <div class="recent-bookings">
            <h5>
                <i class="bi bi-clock-history"></i>
                Lịch sử đặt vé gần đây
            </h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Mã Vé</th>
                            <th>Khách Hàng</th>
                            <th>Tên Phim</th>
                            <th>Thời Gian</th>
                            <th>Trạng Thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span class="ticket-id">#FG-8594</span></td>
                            <td>Nguyễn Văn A</td>
                            <td>Avatar: Dòng Chảy Của Nước</td>
                            <td>14:20 - Hôm nay</td>
                            <td><span class="badge badge-success">Thành công</span></td>
                        </tr>
                        <tr>
                            <td><span class="ticket-id">#FG-8593</span></td>
                            <td>Trần Thị B</td>
                            <td>Conan: Tàu Ngầm Sắt Màu Đen</td>
                            <td>12:05 - Hôm nay</td>
                            <td><span class="badge badge-success">Thành công</span></td>
                        </tr>
                        <tr>
                            <td><span class="ticket-id">#FG-8592</span></td>
                            <td>Lê Văn C</td>
                            <td>Oppenheimer</td>
                            <td>09:15 - Hôm nay</td>
                            <td><span class="badge badge-pending">Chờ thanh toán</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</body>
</html>