<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Thể Loại - FilmGo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
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
            left: 0; top: 0;
            border-right: 1px solid rgba(255,255,255,0.1);
            box-shadow: 2px 0 10px rgba(0,0,0,0.3);
            overflow-y: auto;
        }
        .sidebar-header { padding: 30px 20px; border-bottom: 2px solid rgba(255,255,255,0.1); text-align: center; }
        .sidebar-header .brand { display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 5px; }
        .sidebar-header .brand-icon { font-size: 32px; color: #667eea; }
        .sidebar-header h4 { font-size: 24px; font-weight: 700; background: linear-gradient(135deg, #667eea, #764ba2); -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin: 0; }
        .sidebar-header p { font-size: 12px; color: #cbd5e1; margin: 5px 0 0 0; }
        .sidebar-menu { padding: 25px 0; flex: 1; }
        .nav-link { color: #cbd5e1; font-weight: 500; padding: 14px 20px; margin: 5px 10px; border-radius: 10px; transition: all 0.3s ease; display: flex; align-items: center; gap: 12px; }
        .nav-link:hover { background-color: rgba(102,126,234,0.15); color: #fff; transform: translateX(5px); }
        .nav-link.active { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; font-weight: 600; box-shadow: 0 4px 15px rgba(102,126,234,0.4); }
        .nav-link i { font-size: 18px; width: 20px; }
        .main-content { margin-left: 280px; padding: 40px 30px; min-height: 100vh; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .page-header h1 { font-size: 28px; font-weight: 700; color: #0f172a; margin: 0; }
        .card-table { background: white; border-radius: 15px; padding: 30px; box-shadow: 0 8px 25px rgba(0,0,0,0.08); }
        .search-bar { display: flex; gap: 10px; margin-bottom: 20px; }
        .search-bar input { border-radius: 10px; border: 1px solid #e2e8f0; padding: 10px 16px; flex: 1; max-width: 320px; font-size: 14px; }
        .search-bar input:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102,126,234,0.1); }
        .btn-add { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; color: white; padding: 10px 22px; border-radius: 10px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 15px rgba(102,126,234,0.3); transition: all 0.3s; text-decoration: none; font-size: 14px; }
        .btn-add:hover { opacity: 0.9; color: white; transform: translateY(-1px); }
        .table thead th { background: #f8f9fa; color: #666; font-weight: 600; border: none; padding: 14px 16px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; }
        .table tbody td { padding: 14px 16px; border-bottom: 1px solid #f0f0f0; color: #333; font-weight: 500; vertical-align: middle; }
        .table tbody tr:hover { background-color: #f8f9ff; }
        .badge-genre { background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .btn-action { padding: 6px 14px; border-radius: 8px; font-size: 13px; font-weight: 600; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; transition: all 0.2s; text-decoration: none; }
        .btn-edit { background: #e0e7ff; color: #4338ca; }
        .btn-edit:hover { background: #c7d2fe; color: #3730a3; }
        .btn-delete { background: #fee2e2; color: #dc2626; }
        .btn-delete:hover { background: #fecaca; color: #b91c1c; }
        .movies-count { background: #dcfce7; color: #16a34a; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .alert { border-radius: 10px; padding: 14px 18px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; font-weight: 500; }
        .empty-state { text-align: center; padding: 60px 20px; color: #94a3b8; }
        .empty-state i { font-size: 56px; margin-bottom: 15px; display: block; }
        .pagination .page-link { border-radius: 8px; margin: 0 2px; color: #667eea; border-color: #e2e8f0; }
        .pagination .page-item.active .page-link { background: linear-gradient(135deg, #667eea, #764ba2); border-color: transparent; }
    </style>
</head>
<body>
<div class="d-flex">
    <!-- Sidebar -->
    <div class="sidebar d-flex flex-column">
        <div class="sidebar-header">
            <div class="brand"><i class="bi bi-film brand-icon"></i></div>
            <h4>FILMGO</h4>
            <p>Quản Trị Admin</p>
        </div>
        <div class="sidebar-menu">
            <ul class="nav nav-pills flex-column gap-2 ps-0">
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link">
                        <i class="bi bi-speedometer2"></i><span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="bi bi-film"></i><span>Quản lý phim</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.genres.index') }}" class="nav-link active">
                        <i class="bi bi-tags"></i><span>Quản lý thể loại</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="bi bi-ticket-perforated"></i><span>Quản lý vé đặt</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="bi bi-people"></i><span>Quản lý thành viên</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="bi bi-building"></i><span>Quản lý rạp</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="bi bi-bar-chart"></i><span>Báo cáo thống kê</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <!-- Main Content -->
    <div class="main-content flex-grow-1">
        <div class="page-header">
            <div>
                <h1><i class="bi bi-tags me-2" style="color:#667eea;"></i>Quản Lý Thể Loại Phim</h1>
                <p class="text-muted mt-1 mb-0" style="font-size:14px;">Quản lý các thể loại phim trong hệ thống</p>
            </div>
            <a href="{{ route('admin.genres.create') }}" class="btn-add">
                <i class="bi bi-plus-lg"></i> Thêm Thể Loại
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                <i class="bi bi-check-circle-fill text-success"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-circle-fill text-danger"></i> {{ session('error') }}
            </div>
        @endif

        <div class="card-table">
            <!-- Search -->
            <form method="GET" action="{{ route('admin.genres.index') }}" class="search-bar">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm kiếm thể loại...">
                <button type="submit" class="btn-add" style="padding:10px 18px;">
                    <i class="bi bi-search"></i> Tìm
                </button>
                @if(request('search'))
                    <a href="{{ route('admin.genres.index') }}" class="btn-action btn-edit" style="padding:10px 16px;">
                        <i class="bi bi-x-lg"></i> Xóa lọc
                    </a>
                @endif
            </form>

            @if($genres->isEmpty())
                <div class="empty-state">
                    <i class="bi bi-tags"></i>
                    <p class="fw-semibold fs-5">Chưa có thể loại nào</p>
                    <p>Hãy thêm thể loại phim đầu tiên!</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th style="width:60px;">#</th>
                                <th>Tên Thể Loại</th>
                                <th>Mô Tả</th>
                                <th style="width:140px;">Số Phim</th>
                                <th style="width:160px;">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($genres as $genre)
                                <tr>
                                    <td>{{ $loop->iteration + ($genres->currentPage() - 1) * $genres->perPage() }}</td>
                                    <td>
                                        <span class="badge-genre">{{ $genre->name }}</span>
                                    </td>
                                    <td style="max-width:350px;">
                                        <span class="text-muted" style="font-size:14px;">
                                            {{ $genre->description ?: '—' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="movies-count">
                                            <i class="bi bi-film"></i> {{ $genre->movies_count }} phim
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('admin.genres.edit', $genre) }}" class="btn-action btn-edit">
                                                <i class="bi bi-pencil"></i> Sửa
                                            </a>
                                            <form action="{{ route('admin.genres.destroy', $genre) }}" method="POST"
                                                  onsubmit="return confirm('Bạn có chắc muốn xóa thể loại «{{ $genre->name }}»?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn-action btn-delete">
                                                    <i class="bi bi-trash"></i> Xóa
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <small class="text-muted">
                        Hiển thị {{ $genres->firstItem() }}–{{ $genres->lastItem() }} / {{ $genres->total() }} thể loại
                    </small>
                    {{ $genres->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
</body>
</html>
