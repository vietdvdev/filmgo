<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Phim - FilmGo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); min-height: 100vh; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-attachment: fixed; }
        .sidebar { min-height: 100vh; background: linear-gradient(180deg, #0f172a 0%, #1e293b 50%, #334155 100%); color: #fff; width: 280px; position: fixed; left: 0; top: 0; border-right: 1px solid rgba(255,255,255,0.1); box-shadow: 2px 0 10px rgba(0,0,0,0.3); overflow-y: auto; }
        .sidebar-header { padding: 30px 20px; border-bottom: 2px solid rgba(255,255,255,0.1); text-align: center; }
        .sidebar-header .brand { display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 5px; }
        .sidebar-header .brand-icon { font-size: 32px; color: #667eea; }
        .sidebar-header h4 { font-size: 24px; font-weight: 700; background: linear-gradient(135deg, #667eea, #764ba2); -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin: 0; }
        .sidebar-header p { font-size: 12px; color: #cbd5e1; margin: 5px 0 0 0; }
        .sidebar-menu { padding: 25px 0; }
        .nav-link { color: #cbd5e1; font-weight: 500; padding: 14px 20px; margin: 5px 10px; border-radius: 10px; transition: all 0.3s ease; display: flex; align-items: center; gap: 12px; }
        .nav-link:hover { background-color: rgba(102,126,234,0.15); color: #fff; transform: translateX(5px); }
        .nav-link.active { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; font-weight: 600; box-shadow: 0 4px 15px rgba(102,126,234,0.4); }
        .nav-link i { font-size: 18px; width: 20px; }
        .main-content { margin-left: 280px; padding: 40px 30px; min-height: 100vh; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .page-header h1 { font-size: 28px; font-weight: 700; color: #0f172a; margin: 0; }
        .card-table { background: white; border-radius: 15px; padding: 25px 30px; box-shadow: 0 8px 25px rgba(0,0,0,0.08); }
        .filter-bar { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 20px; }
        .filter-bar input, .filter-bar select { border-radius: 10px; border: 1px solid #e2e8f0; padding: 9px 14px; font-size: 14px; }
        .filter-bar input:focus, .filter-bar select:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102,126,234,0.1); }
        .filter-bar input { width: 220px; }
        .btn-add { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; color: white; padding: 10px 20px; border-radius: 10px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 15px rgba(102,126,234,0.3); transition: all 0.3s; text-decoration: none; font-size: 14px; }
        .btn-add:hover { opacity: 0.9; color: white; transform: translateY(-1px); }
        .btn-filter { background: #f1f5f9; color: #475569; padding: 9px 16px; border-radius: 10px; font-weight: 600; font-size: 14px; border: 1px solid #e2e8f0; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; }
        .btn-filter:hover { background: #e2e8f0; color: #374151; }

        /* ===== TABLE ===== */
        .table { table-layout: fixed; width: 100%; }
        .table thead th { background: #f8f9fa; color: #666; font-weight: 600; border: none; padding: 12px 10px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap; overflow: hidden; }
        .table tbody td { padding: 12px 10px; border-bottom: 1px solid #f0f0f0; color: #333; vertical-align: middle; }
        .table tbody tr:hover { background-color: #f8f9ff; }

        /* Cột cố định */
        .col-no     { width: 36px; }
        .col-poster { width: 58px; }
        .col-title  { width: 22%; }
        .col-genre  { width: 17%; }
        .col-dur    { width: 82px; }
        .col-date   { width: 90px; }
        .col-age    { width: 62px; }
        .col-status { width: 108px; }
        .col-action { width: 120px; }

        .poster-thumb { width: 42px; height: 60px; object-fit: cover; border-radius: 6px; display: block; }
        .poster-placeholder { width: 42px; height: 60px; background: #e2e8f0; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: #94a3b8; font-size: 18px; }

        .movie-title { font-weight: 600; color: #1e293b; font-size: 13px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .movie-meta  { font-size: 11px; color: #94a3b8; margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        .genre-tag { background: #ede9fe; color: #6d28d9; padding: 2px 7px; border-radius: 10px; font-size: 10px; font-weight: 600; display: inline-block; margin: 1px; white-space: nowrap; }

        .badge-age { padding: 3px 7px; border-radius: 6px; font-size: 11px; font-weight: 700; background: #1e293b; color: #fff; white-space: nowrap; }

        .badge-status { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; white-space: nowrap; display: inline-block; }
        .status-showing  { background: #dcfce7; color: #16a34a; }
        .status-upcoming { background: #dbeafe; color: #1d4ed8; }
        .status-stopped  { background: #fee2e2; color: #dc2626; }
        .status-deleted  { background: #f3f4f6; color: #9ca3af; }

        .td-action { white-space: nowrap; }
        .btn-action { padding: 5px 10px; border-radius: 7px; font-size: 12px; font-weight: 600; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; transition: all 0.2s; text-decoration: none; white-space: nowrap; vertical-align: middle; }
        .btn-edit    { background: #e0e7ff; color: #4338ca; }
        .btn-edit:hover    { background: #c7d2fe; color: #3730a3; }
        .btn-delete  { background: #fee2e2; color: #dc2626; }
        .btn-delete:hover  { background: #fecaca; color: #b91c1c; }
        .btn-restore { background: #dcfce7; color: #16a34a; }
        .btn-restore:hover { background: #bbf7d0; color: #15803d; }

        .alert { border-radius: 10px; padding: 13px 18px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; font-weight: 500; }
        .empty-state { text-align: center; padding: 60px 20px; color: #94a3b8; }
        .empty-state i { font-size: 56px; margin-bottom: 15px; display: block; }
        .pagination .page-link { border-radius: 8px; margin: 0 2px; color: #667eea; border-color: #e2e8f0; }
        .pagination .page-item.active .page-link { background: linear-gradient(135deg, #667eea, #764ba2); border-color: transparent; }
        .deleted-row { opacity: 0.6; }
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
                <li><a href="{{ route('admin.dashboard') }}" class="nav-link"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a></li>
                <li><a href="{{ route('admin.movies.index') }}" class="nav-link active"><i class="bi bi-film"></i><span>Quản lý phim</span></a></li>
                <li><a href="{{ route('admin.genres.index') }}" class="nav-link"><i class="bi bi-tags"></i><span>Quản lý thể loại</span></a></li>
                <li><a href="#" class="nav-link"><i class="bi bi-ticket-perforated"></i><span>Quản lý vé đặt</span></a></li>
                <li><a href="#" class="nav-link"><i class="bi bi-people"></i><span>Quản lý thành viên</span></a></li>
                <li><a href="#" class="nav-link"><i class="bi bi-building"></i><span>Quản lý rạp</span></a></li>
                <li><a href="#" class="nav-link"><i class="bi bi-bar-chart"></i><span>Báo cáo thống kê</span></a></li>
            </ul>
        </div>
    </div>

    <!-- Main -->
    <div class="main-content flex-grow-1">
        <div class="page-header">
            <div>
                <h1><i class="bi bi-film me-2" style="color:#667eea;"></i>Quản Lý Phim</h1>
                <p class="text-muted mt-1 mb-0" style="font-size:14px;">Tổng: {{ $movies->total() }} phim</p>
            </div>
            <a href="{{ route('admin.movies.create') }}" class="btn-add">
                <i class="bi bi-plus-lg"></i> Thêm Phim Mới
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success"><i class="bi bi-check-circle-fill text-success"></i> {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger"><i class="bi bi-exclamation-circle-fill text-danger"></i> {{ session('error') }}</div>
        @endif

        <div class="card-table">
            <!-- Filter -->
            <form method="GET" action="{{ route('admin.movies.index') }}" class="filter-bar">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm tên phim...">
                <select name="status">
                    <option value="">Tất cả trạng thái</option>
                    <option value="showing"  {{ request('status') == 'showing'  ? 'selected' : '' }}>Đang chiếu</option>
                    <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>Sắp chiếu</option>
                    <option value="stopped"  {{ request('status') == 'stopped'  ? 'selected' : '' }}>Ngừng chiếu</option>
                </select>
                <select name="genre">
                    <option value="">Tất cả thể loại</option>
                    @foreach($genres as $g)
                        <option value="{{ $g->id }}" {{ request('genre') == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-add" style="padding:9px 16px;"><i class="bi bi-search"></i> Lọc</button>
                @if(request()->hasAny(['search','status','genre']))
                    <a href="{{ route('admin.movies.index') }}" class="btn-filter"><i class="bi bi-x-lg"></i> Xóa lọc</a>
                @endif
            </form>

            @if($movies->isEmpty())
                <div class="empty-state">
                    <i class="bi bi-film"></i>
                    <p class="fw-semibold fs-5">Chưa có phim nào</p>
                    <p>Hãy thêm phim đầu tiên!</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th class="col-no">#</th>
                                <th class="col-poster">Poster</th>
                                <th class="col-title">Tên Phim</th>
                                <th class="col-genre">Thể Loại</th>
                                <th class="col-dur">T.Lượng</th>
                                <th class="col-date">Khởi Chiếu</th>
                                <th class="col-age">Tuổi</th>
                                <th class="col-status">Trạng Thái</th>
                                <th class="col-action">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($movies as $movie)
                                <tr class="{{ $movie->trashed() ? 'deleted-row' : '' }}">
                                    <td>{{ $loop->iteration + ($movies->currentPage() - 1) * $movies->perPage() }}</td>
                                    <td>
                                        @if($movie->poster)
                                            <img src="{{ asset($movie->poster) }}" alt="{{ $movie->title }}" class="poster-thumb">
                                        @else
                                            <div class="poster-placeholder"><i class="bi bi-image"></i></div>
                                        @endif
                                    </td>
                                    <td style="max-width:0;">
                                        <div class="movie-title" title="{{ $movie->title }}">{{ $movie->title }}</div>
                                        <div class="movie-meta">
                                            {{ $movie->director ? '🎬 '.$movie->director : '' }}{{ $movie->country ? ' · '.$movie->country : '' }}
                                        </div>
                                    </td>
                                    <td style="max-width:0;">
                                        @foreach($movie->genres as $genre)
                                            <span class="genre-tag">{{ $genre->name }}</span>
                                        @endforeach
                                    </td>
                                    <td>{{ $movie->duration }} phút</td>
                                    <td>{{ $movie->release_date->format('d/m/Y') }}</td>
                                    <td><span class="badge-age">{{ $movie->age_limit }}</span></td>
                                    <td>
                                        @if($movie->trashed())
                                            <span class="badge-status status-deleted">Đã xóa</span>
                                        @elseif($movie->status === 'showing')
                                            <span class="badge-status status-showing">Đang chiếu</span>
                                        @elseif($movie->status === 'upcoming')
                                            <span class="badge-status status-upcoming">Sắp chiếu</span>
                                        @else
                                            <span class="badge-status status-stopped">Ngừng chiếu</span>
                                        @endif
                                    </td>
                                    <td class="td-action">
                                        @if($movie->trashed())
                                            <form action="{{ route('admin.movies.restore', $movie->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn-action btn-restore">
                                                    <i class="bi bi-arrow-counterclockwise"></i> Khôi phục
                                                </button>
                                            </form>
                                        @else
                                            <a href="{{ route('admin.movies.edit', $movie) }}" class="btn-action btn-edit">
                                                <i class="bi bi-pencil"></i> Sửa
                                            </a>
                                            <form action="{{ route('admin.movies.destroy', $movie) }}" method="POST" class="d-inline"
                                                  onsubmit="return confirm('Xóa phim «{{ addslashes($movie->title) }}»?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn-action btn-delete">
                                                    <i class="bi bi-trash"></i> Xóa
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <small class="text-muted">Hiển thị {{ $movies->firstItem() }}–{{ $movies->lastItem() }} / {{ $movies->total() }} phim</small>
                    {{ $movies->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
</body>
</html>
