<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Thể Loại - FilmGo</title>
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
        .page-header { margin-bottom: 30px; }
        .page-header h1 { font-size: 28px; font-weight: 700; color: #0f172a; margin: 0; }
        .breadcrumb-link { color: #667eea; text-decoration: none; font-size: 14px; }
        .breadcrumb-link:hover { text-decoration: underline; }
        .form-card { background: white; border-radius: 15px; padding: 35px; box-shadow: 0 8px 25px rgba(0,0,0,0.08); max-width: 680px; }
        .form-label { font-weight: 600; color: #374151; font-size: 14px; margin-bottom: 6px; }
        .form-control { border-radius: 10px; border: 1px solid #e2e8f0; padding: 11px 16px; font-size: 14px; transition: all 0.2s; }
        .form-control:focus { border-color: #667eea; box-shadow: 0 0 0 3px rgba(102,126,234,0.12); }
        .form-control.is-invalid { border-color: #dc2626; }
        .invalid-feedback { font-size: 13px; color: #dc2626; }
        .quick-tags { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
        .quick-tag { background: #f1f5f9; color: #475569; padding: 5px 14px; border-radius: 20px; font-size: 13px; cursor: pointer; border: 1px solid #e2e8f0; transition: all 0.2s; }
        .quick-tag:hover { background: #667eea; color: white; border-color: #667eea; }
        .btn-submit { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; color: white; padding: 12px 30px; border-radius: 10px; font-weight: 600; font-size: 15px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 15px rgba(102,126,234,0.3); transition: all 0.3s; }
        .btn-submit:hover { opacity: 0.9; transform: translateY(-1px); }
        .btn-cancel { background: #f1f5f9; color: #475569; padding: 12px 24px; border-radius: 10px; font-weight: 600; font-size: 15px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s; border: 1px solid #e2e8f0; }
        .btn-cancel:hover { background: #e2e8f0; color: #374151; }
        .char-count { font-size: 12px; color: #94a3b8; text-align: right; margin-top: 4px; }
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
            <div class="d-flex align-items-center gap-2 mb-2">
                <a href="{{ route('admin.genres.index') }}" class="breadcrumb-link">
                    <i class="bi bi-tags"></i> Quản Lý Thể Loại
                </a>
                <i class="bi bi-chevron-right text-muted" style="font-size:12px;"></i>
                <span style="font-size:14px; color:#94a3b8;">Thêm Mới</span>
            </div>
            <h1><i class="bi bi-plus-circle me-2" style="color:#667eea;"></i>Thêm Thể Loại Mới</h1>
        </div>

        <div class="form-card">
            <form action="{{ route('admin.genres.store') }}" method="POST">
                @csrf

                <!-- Tên thể loại -->
                <div class="mb-4">
                    <label for="name" class="form-label">
                        Tên Thể Loại <span class="text-danger">*</span>
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name') }}"
                        class="form-control @error('name') is-invalid @enderror"
                        placeholder="Ví dụ: Hành động, Kinh dị, Tình cảm..."
                        maxlength="100"
                        oninput="updateCharCount(this, 'nameCount', 100)"
                    >
                    <div class="char-count"><span id="nameCount">{{ strlen(old('name', '')) }}</span>/100</div>
                    @error('name')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror

                    <!-- Gợi ý nhanh -->
                    <div class="mt-2">
                        <small class="text-muted">Gợi ý nhanh:</small>
                        <div class="quick-tags">
                            @foreach(['Hành động','Tình cảm','Kinh dị','Hoạt hình','Viễn tưởng','Hài hước','Tâm lý','Phiêu lưu','Tội phạm','Lịch sử'] as $tag)
                                <span class="quick-tag" onclick="document.getElementById('name').value='{{ $tag }}'; updateCharCount(document.getElementById('name'), 'nameCount', 100)">
                                    {{ $tag }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Mô tả -->
                <div class="mb-4">
                    <label for="description" class="form-label">Mô Tả</label>
                    <textarea
                        id="description"
                        name="description"
                        rows="4"
                        class="form-control @error('description') is-invalid @enderror"
                        placeholder="Mô tả ngắn về thể loại phim này..."
                    >{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-3 mt-4">
                    <button type="submit" class="btn-submit">
                        <i class="bi bi-check-lg"></i> Lưu Thể Loại
                    </button>
                    <a href="{{ route('admin.genres.index') }}" class="btn-cancel">
                        <i class="bi bi-x-lg"></i> Hủy
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function updateCharCount(input, countId, max) {
        document.getElementById(countId).textContent = input.value.length;
    }
</script>
</body>
</html>
