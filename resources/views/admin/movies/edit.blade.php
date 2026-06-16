<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa Phim - FilmGo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); min-height: 100vh; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-attachment: fixed; }
        .sidebar { min-height: 100vh; background: linear-gradient(180deg, #0f172a 0%, #1e293b 50%, #334155 100%); color: #fff; width: 280px; position: fixed; left: 0; top: 0; overflow-y: auto; }
        .sidebar-header { padding: 30px 20px; border-bottom: 2px solid rgba(255,255,255,0.1); text-align: center; }
        .sidebar-header .brand { display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 5px; }
        .sidebar-header .brand-icon { font-size: 32px; color: #667eea; }
        .sidebar-header h4 { font-size: 24px; font-weight: 700; background: linear-gradient(135deg, #667eea, #764ba2); -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin: 0; }
        .sidebar-header p { font-size: 12px; color: #cbd5e1; margin: 5px 0 0 0; }
        .sidebar-menu { padding: 25px 0; }
        .nav-link { color: #cbd5e1; font-weight: 500; padding: 14px 20px; margin: 5px 10px; border-radius: 10px; transition: all 0.3s; display: flex; align-items: center; gap: 12px; }
        .nav-link:hover { background-color: rgba(102,126,234,0.15); color: #fff; transform: translateX(5px); }
        .nav-link.active { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; font-weight: 600; box-shadow: 0 4px 15px rgba(102,126,234,0.4); }
        .nav-link i { font-size: 18px; width: 20px; }
        .main-content { margin-left: 280px; padding: 40px 30px; min-height: 100vh; }
        .breadcrumb-link { color: #667eea; text-decoration: none; font-size: 14px; }
        .breadcrumb-link:hover { text-decoration: underline; }
        .page-header { margin-bottom: 28px; }
        .page-header h1 { font-size: 26px; font-weight: 700; color: #0f172a; margin: 0; }
        .form-section { background: white; border-radius: 15px; padding: 28px 30px; box-shadow: 0 8px 25px rgba(0,0,0,0.08); margin-bottom: 20px; }
        .section-title { font-size: 15px; font-weight: 700; color: #374151; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #f1f5f9; display: flex; align-items: center; gap: 8px; }
        .section-title i { color: #667eea; }
        .form-label { font-weight: 600; color: #374151; font-size: 13px; margin-bottom: 5px; }
        .form-control, .form-select { border-radius: 10px; border: 1px solid #e2e8f0; padding: 10px 14px; font-size: 14px; transition: all 0.2s; }
        .form-control:focus, .form-select:focus { border-color: #667eea; box-shadow: 0 0 0 3px rgba(102,126,234,0.12); }
        .form-control.is-invalid, .form-select.is-invalid { border-color: #dc2626; }
        .invalid-feedback { font-size: 12px; color: #dc2626; display: block; margin-top: 4px; }
        .poster-preview { width: 120px; height: 170px; object-fit: cover; border-radius: 10px; border: 2px solid #e2e8f0; }
        .poster-placeholder { width: 120px; height: 170px; background: #f1f5f9; border-radius: 10px; border: 2px dashed #cbd5e1; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #94a3b8; font-size: 12px; gap: 6px; }
        .poster-placeholder i { font-size: 36px; }
        .checkbox-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 8px; }
        .checkbox-item { display: flex; align-items: center; gap: 8px; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 8px; cursor: pointer; transition: all 0.2s; }
        .checkbox-item:hover { border-color: #667eea; background: #f5f3ff; }
        .checkbox-item:has(input:checked) { border-color: #667eea; background: #ede9fe; }
        .checkbox-item label { font-size: 13px; cursor: pointer; margin: 0; color: #374151; }
        .actor-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); gap: 8px; max-height: 260px; overflow-y: auto; padding-right: 4px; }
        .actor-item { display: flex; align-items: center; gap: 8px; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 8px; cursor: pointer; transition: all 0.2s; }
        .actor-item:hover { border-color: #667eea; background: #f5f3ff; }
        .actor-item:has(input:checked) { border-color: #667eea; background: #ede9fe; }
        .actor-item label { font-size: 13px; cursor: pointer; margin: 0; color: #374151; }
        .actor-grid::-webkit-scrollbar { width: 6px; }
        .actor-grid::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 3px; }
        .actor-grid::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        .btn-submit { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; color: white; padding: 12px 32px; border-radius: 10px; font-weight: 600; font-size: 15px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 15px rgba(102,126,234,0.3); transition: all 0.3s; }
        .btn-submit:hover { opacity: 0.9; transform: translateY(-1px); }
        .btn-cancel { background: #f1f5f9; color: #475569; padding: 12px 24px; border-radius: 10px; font-weight: 600; font-size: 15px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; border: 1px solid #e2e8f0; transition: all 0.2s; }
        .btn-cancel:hover { background: #e2e8f0; color: #374151; }
        .btn-danger-outline { background: #fff1f2; color: #e11d48; padding: 12px 22px; border-radius: 10px; font-weight: 600; font-size: 14px; border: 1px solid #fecdd3; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; transition: all 0.2s; width: 100%; justify-content: center; }
        .btn-danger-outline:hover { background: #ffe4e6; }
        .age-options { display: flex; flex-wrap: wrap; gap: 8px; }
        .age-opt { display: none; }
        .age-label { padding: 8px 18px; border: 2px solid #e2e8f0; border-radius: 8px; cursor: pointer; font-weight: 700; font-size: 13px; color: #64748b; transition: all 0.2s; }
        .age-opt:checked + .age-label { border-color: #667eea; background: #ede9fe; color: #6d28d9; }
        .info-badge { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px; padding: 10px 14px; display: flex; align-items: center; gap: 8px; font-size: 13px; color: #1e40af; margin-bottom: 20px; }
        .btn-add-actor { background: #f1f5f9; color: #475569; border: 1.5px dashed #cbd5e1; padding: 8px 18px; border-radius: 10px; font-size: 13px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s; }
        .btn-add-actor:hover { background: #ede9fe; color: #6d28d9; border-color: #a78bfa; }
        .add-actor-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px; margin-bottom: 12px; }
        .btn-actor-confirm { background: linear-gradient(135deg, #667eea, #764ba2); color: white; border: none; padding: 7px 18px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; }
        .btn-actor-confirm:hover { opacity: 0.9; }
        .btn-actor-cancel { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; padding: 7px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; }
        .btn-actor-cancel:hover { background: #e2e8f0; }
        .actor-tags { display: flex; flex-wrap: wrap; gap: 8px; min-height: 10px; }
        .actor-tag { background: #ede9fe; color: #6d28d9; padding: 5px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; }
        .actor-tag button { background: none; border: none; color: #a78bfa; cursor: pointer; padding: 0; font-size: 14px; line-height: 1; display: flex; align-items: center; }
        .actor-tag button:hover { color: #dc2626; }
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
            <div class="d-flex align-items-center gap-2 mb-2">
                <a href="{{ route('admin.movies.index') }}" class="breadcrumb-link"><i class="bi bi-film"></i> Quản Lý Phim</a>
                <i class="bi bi-chevron-right text-muted" style="font-size:12px;"></i>
                <span style="font-size:14px; color:#94a3b8;">Chỉnh Sửa</span>
            </div>
            <h1><i class="bi bi-pencil-square me-2" style="color:#667eea;"></i>Chỉnh Sửa Phim</h1>
        </div>

        <form action="{{ route('admin.movies.update', $movie) }}" method="POST" enctype="multipart/form-data" id="movieForm">
            @csrf @method('PUT')

            <div class="row g-4">
                <!-- Cột trái -->
                <div class="col-lg-8">

                    <!-- Thông tin cơ bản -->
                    <div class="form-section">
                        <div class="info-badge">
                            <i class="bi bi-pencil-fill"></i>
                            Đang chỉnh sửa: <strong>{{ $movie->title }}</strong>
                        </div>
                        <div class="section-title"><i class="bi bi-info-circle-fill"></i> Thông Tin Cơ Bản</div>

                        <div class="mb-3">
                            <label class="form-label">Tên Phim <span class="text-danger">*</span></label>
                            <input type="text" name="title" value="{{ old('title', $movie->title) }}"
                                class="form-control @error('title') is-invalid @enderror"
                                placeholder="Nhập tên phim...">
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label">Đạo Diễn</label>
                                <input type="text" name="director" value="{{ old('director', $movie->director) }}"
                                    class="form-control" placeholder="Tên đạo diễn...">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label">Quốc Gia</label>
                                <input type="text" name="country" value="{{ old('country', $movie->country) }}"
                                    class="form-control" placeholder="Việt Nam, Mỹ, Hàn Quốc...">
                            </div>
                        </div>

                        <div class="row g-3 mt-1">
                            <div class="col-sm-4">
                                <label class="form-label">Thời Lượng (phút) <span class="text-danger">*</span></label>
                                <input type="number" name="duration" value="{{ old('duration', $movie->duration) }}"
                                    class="form-control @error('duration') is-invalid @enderror"
                                    placeholder="90" min="1" max="600">
                                @error('duration')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-sm-4">
                                <label class="form-label">Ngày Khởi Chiếu <span class="text-danger">*</span></label>
                                <input type="date" name="release_date" value="{{ old('release_date', $movie->release_date->format('Y-m-d')) }}"
                                    class="form-control @error('release_date') is-invalid @enderror">
                                @error('release_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-sm-4">
                                <label class="form-label">Trạng Thái <span class="text-danger">*</span></label>
                                <select name="status" class="form-select @error('status') is-invalid @enderror">
                                    <option value="upcoming" {{ old('status', $movie->status) == 'upcoming' ? 'selected' : '' }}>Sắp chiếu</option>
                                    <option value="showing"  {{ old('status', $movie->status) == 'showing'  ? 'selected' : '' }}>Đang chiếu</option>
                                    <option value="stopped"  {{ old('status', $movie->status) == 'stopped'  ? 'selected' : '' }}>Ngừng chiếu</option>
                                </select>
                                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="form-label">Giới Hạn Độ Tuổi <span class="text-danger">*</span></label>
                            <div class="age-options">
                                @foreach(['P' => 'P - Mọi lứa tuổi', 'K' => 'K - Dưới 13 có phụ huynh', 'T13' => 'T13 - Từ 13 tuổi', 'T16' => 'T16 - Từ 16 tuổi', 'T18' => 'T18 - Từ 18 tuổi'] as $val => $label)
                                    <input type="radio" name="age_limit" id="age_{{ $val }}" value="{{ $val }}" class="age-opt"
                                        {{ old('age_limit', $movie->age_limit) == $val ? 'checked' : '' }}>
                                    <label for="age_{{ $val }}" class="age-label">{{ $val }}</label>
                                @endforeach
                            </div>
                            @error('age_limit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mt-3">
                            <label class="form-label">Mô Tả Nội Dung</label>
                            <textarea name="description" rows="4" class="form-control"
                                placeholder="Tóm tắt nội dung phim...">{{ old('description', $movie->description) }}</textarea>
                        </div>
                    </div>

                    <!-- Poster & Trailer -->
                    <div class="form-section">
                        <div class="section-title"><i class="bi bi-image-fill"></i> Poster & Trailer</div>
                        <div class="d-flex gap-4 align-items-start">
                            <!-- Preview -->
                            <div class="flex-shrink-0">
                                @if($movie->poster)
                                    <img id="posterImg" class="poster-preview" src="{{ asset($movie->poster) }}" alt="Poster">
                                    <div class="poster-placeholder d-none" id="placeholderDiv"><i class="bi bi-image"></i><span>Xem trước</span></div>
                                @else
                                    <img id="posterImg" class="poster-preview d-none" src="" alt="Poster">
                                    <div class="poster-placeholder" id="placeholderDiv"><i class="bi bi-image"></i><span>Xem trước</span></div>
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <div class="mb-3">
                                    <label class="form-label">Tải Lên Poster Mới <small class="text-muted fw-normal">(jpg, png, webp — tối đa 2MB)</small></label>
                                    <input type="file" name="poster" id="posterInput"
                                        class="form-control @error('poster') is-invalid @enderror"
                                        accept="image/jpeg,image/png,image/webp"
                                        onchange="previewPoster(this)">
                                    @error('poster')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    @if($movie->poster)
                                        <div class="mt-2 d-flex align-items-center gap-2">
                                            <input type="checkbox" name="remove_poster" value="1" id="removePoster"
                                                onchange="toggleRemovePoster(this)">
                                            <label for="removePoster" class="text-danger" style="font-size:13px; cursor:pointer;">
                                                <i class="bi bi-trash"></i> Xóa poster hiện tại
                                            </label>
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <label class="form-label">URL Trailer</label>
                                    <input type="url" name="trailer_url" value="{{ old('trailer_url', $movie->trailer_url) }}"
                                        class="form-control @error('trailer_url') is-invalid @enderror"
                                        placeholder="https://youtube.com/watch?v=...">
                                    @error('trailer_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Thể loại -->
                    <div class="form-section">
                        <div class="section-title"><i class="bi bi-tags-fill"></i> Thể Loại</div>
                        <div class="checkbox-grid">
                            @foreach($genres as $genre)
                                <div class="checkbox-item">
                                    <input type="checkbox" name="genres[]" id="genre_{{ $genre->id }}"
                                        value="{{ $genre->id }}"
                                        {{ in_array($genre->id, old('genres', $selectedGenres)) ? 'checked' : '' }}>
                                    <label for="genre_{{ $genre->id }}">{{ $genre->name }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Diễn viên -->
                    <div class="form-section">
                        <div class="section-title"><i class="bi bi-person-badge-fill"></i> Diễn Viên</div>

                        <div id="actorList" class="actor-tags mb-3"></div>

                        <div id="addActorBox" class="add-actor-box d-none">
                            <input type="text" id="actorNameInput" class="form-control" placeholder="Nhập tên diễn viên..." style="max-width:280px;">
                            <div class="d-flex gap-2 mt-2">
                                <button type="button" class="btn-actor-confirm" onclick="confirmActor()"><i class="bi bi-check-lg"></i> Xác nhận</button>
                                <button type="button" class="btn-actor-cancel" onclick="cancelActor()"><i class="bi bi-x-lg"></i> Hủy</button>
                            </div>
                        </div>

                        <button type="button" class="btn-add-actor" id="addActorBtn" onclick="showAddActor()">
                            <i class="bi bi-plus-circle"></i> Thêm Diễn Viên
                        </button>

                        <div id="actorInputs"></div>
                    </div>

                </div>

                <!-- Cột phải -->
                <div class="col-lg-4">
                    <div class="form-section" style="position:sticky; top:20px;">
                        <div class="section-title"><i class="bi bi-check2-circle"></i> Xác Nhận</div>

                        @if($errors->any())
                            <div class="alert alert-danger p-3 rounded-3 mb-3" style="font-size:13px;">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                <strong>Vui lòng sửa các lỗi:</strong>
                                <ul class="mb-0 mt-1 ps-3">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="d-flex flex-column gap-3 mt-2">
                            <button type="submit" class="btn-submit w-100 justify-content-center">
                                <i class="bi bi-check-lg"></i> Cập Nhật Phim
                            </button>
                            <a href="{{ route('admin.movies.index') }}" class="btn-cancel w-100 justify-content-center">
                                <i class="bi bi-x-lg"></i> Hủy
                            </a>
                        </div>

                        <hr class="my-4" style="border-color:#f1f5f9;">
                        <button type="submit" form="deleteForm" class="btn-danger-outline"
                            onclick="return confirm('Xóa phim «{{ addslashes($movie->title) }}»? Có thể khôi phục sau.')">
                            <i class="bi bi-trash"></i> Xóa Phim Này
                        </button>
                    </div>
                </div>
            </div>
        </form>

        {{-- Form xóa đặt ngoài form update --}}
        <form id="deleteForm" action="{{ route('admin.movies.destroy', $movie) }}" method="POST">
            @csrf @method('DELETE')
        </form>
    </div>
</div>

<script>
    // Khởi tạo danh sách diễn viên từ dữ liệu có sẵn
    let actors = @json($movie->actors->pluck('name'));

    function showAddActor() {
        document.getElementById('addActorBox').classList.remove('d-none');
        document.getElementById('addActorBtn').classList.add('d-none');
        document.getElementById('actorNameInput').value = '';
        document.getElementById('actorNameInput').focus();
    }

    function cancelActor() {
        document.getElementById('addActorBox').classList.add('d-none');
        document.getElementById('addActorBtn').classList.remove('d-none');
    }

    function confirmActor() {
        const name = document.getElementById('actorNameInput').value.trim();
        if (!name) { document.getElementById('actorNameInput').focus(); return; }
        if (actors.find(a => a.toLowerCase() === name.toLowerCase())) {
            alert('Diễn viên này đã được thêm!'); return;
        }
        actors.push(name);
        renderActors();
        cancelActor();
    }

    function removeActor(name) {
        actors = actors.filter(a => a !== name);
        renderActors();
    }

    function renderActors() {
        const list = document.getElementById('actorList');
        const inputs = document.getElementById('actorInputs');
        list.innerHTML = actors.map(name =>
            `<span class="actor-tag">${name}<button type="button" onclick="removeActor('${name.replace(/'/g, "\\'")}')" title="Xóa"><i class="bi bi-x"></i></button></span>`
        ).join('');
        inputs.innerHTML = actors.map(name =>
            `<input type="hidden" name="actor_names[]" value="${name}">`
        ).join('');
    }

    function previewPoster(input) {
        const img = document.getElementById('posterImg');
        const placeholder = document.getElementById('placeholderDiv');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => {
                img.src = e.target.result;
                img.classList.remove('d-none');
                placeholder.classList.add('d-none');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function toggleRemovePoster(checkbox) {
        const img = document.getElementById('posterImg');
        const placeholder = document.getElementById('placeholderDiv');
        if (checkbox.checked) {
            img.classList.add('d-none');
            placeholder.classList.remove('d-none');
        } else {
            img.classList.remove('d-none');
            placeholder.classList.add('d-none');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        renderActors();
        document.getElementById('actorNameInput').addEventListener('keydown', e => {
            if (e.key === 'Enter') { e.preventDefault(); confirmActor(); }
            if (e.key === 'Escape') cancelActor();
        });
    });
</script>
</body>
</html>
