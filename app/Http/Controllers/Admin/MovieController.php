<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Actor;
use App\Models\Format;
use App\Models\Genre;
use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MovieController extends Controller
{
    public function index(Request $request)
    {
        $tabFilter = $request->input('tab', 'active');

        if ($tabFilter === 'trash') {
            // Eager load format thay vì formats
            $query = Movie::onlyTrashed()->with(['genres', 'format']);
        } else {
            $query = Movie::query()->with(['genres', 'format']);
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('genre')) {
            $query->whereHas('genres', fn($q) => $q->where('genres.id', $request->genre));
        }

        $movies = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
        $genres = Genre::orderBy('name')->get();
        
        $activeCount = Movie::count();
        $trashCount = Movie::onlyTrashed()->count();

        return view('admin.movies.index', compact('movies', 'genres', 'activeCount', 'trashCount', 'tabFilter'));
    }

    public function create()
    {
        $genres  = Genre::orderBy('name')->get();
        $formats = Format::orderBy('id')->get();
        return view('admin.movies.create', compact('genres', 'formats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'         => 'required|string|max:255',
            'duration'      => 'required|integer|min:1|max:600',
            'release_date'  => 'required|date',
            'age_limit'     => 'required|in:P,K,T13,T16,T18',
            'status'        => 'required|in:upcoming,showing,stopped',
            'poster'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'trailer_url'   => 'nullable|url|max:255',
            'director'      => 'nullable|string|max:255',
            'country'       => 'nullable|string|max:100',
            'description'   => 'nullable|string',
            'genres'        => 'nullable|array',
            'genres.*'      => 'exists:genres,id',
            'actor_names'   => 'nullable|array',
            'actor_names.*' => 'nullable|string|max:255',
            // Định dạng chiếu: bắt buộc, là số nguyên và phải tồn tại trong bảng formats
            'format_id'     => 'required|integer|exists:formats,id',
        ], [
            'title.required'        => 'Tên phim không được để trống.',
            'duration.required'     => 'Thời lượng không được để trống.',
            'duration.integer'      => 'Thời lượng phải là số nguyên.',
            'release_date.required' => 'Ngày khởi chiếu không được để trống.',
            'age_limit.required'    => 'Phân loại độ tuổi không được để trống.',
            'status.required'       => 'Trạng thái không được để trống.',
            'poster.image'          => 'File poster phải là hình ảnh.',
            'poster.mimes'          => 'Poster chỉ chấp nhận định dạng jpg, jpeg, png, webp.',
            'poster.max'            => 'Poster không được vượt quá 2MB.',
            'format_id.required'    => 'Vui lòng chọn một định dạng chiếu.',
            'format_id.integer'     => 'Định dạng chiếu không hợp lệ.',
            'format_id.exists'      => 'Định dạng chiếu không tồn tại.',
        ]);

        $posterPath = null;
        if ($request->hasFile('poster')) {
            $posterPath = 'storage/' . $request->file('poster')->store('posters', 'public');
        }

        $movie = Movie::create([
            'title'        => $request->title,
            'slug'         => $this->uniqueSlug($request->title),
            'duration'     => $request->duration,
            'release_date' => $request->release_date,
            'age_limit'    => $request->age_limit,
            'status'       => $request->status,
            'poster'       => $posterPath,
            'trailer_url'  => $request->trailer_url,
            'director'     => $request->director,
            'country'      => $request->country,
            'description'  => $request->description,
            // Lưu trực tiếp format_id vào bảng movies
            'format_id'    => $request->format_id,
        ]);

        if ($request->genres) {
            $movie->genres()->attach($request->genres);
        }

        // Bỏ qua việc đính kèm vào bảng pivot vì đã lưu format_id ở trên


        $this->syncActorsByName($movie, $request->actor_names ?? []);

        return redirect()->route('admin.movies.index')->with('success', 'Thêm phim thành công!');
    }

    public function edit(Movie $movie)
    {
        // Eager load relations để tránh N+1 query khi view render (dùng format thay vì formats)
        $movie->loadMissing(['genres', 'format', 'actors']);

        $genres          = Genre::orderBy('name')->get();
        $formats         = Format::orderBy('id')->get();
        $selectedGenres  = $movie->genres->pluck('id')->toArray();
        // Lấy format_id hiện tại để hiển thị trên giao diện (chỉ 1 giá trị)
        $selectedFormat  = $movie->format_id;
        
        return view('admin.movies.edit', compact('movie', 'genres', 'selectedGenres', 'formats', 'selectedFormat'));
    }

    public function update(Request $request, Movie $movie)
    {
        $request->validate([
            'title'         => 'required|string|max:255',
            'duration'      => 'required|integer|min:1|max:600',
            'release_date'  => 'required|date',
            'age_limit'     => 'required|in:P,K,T13,T16,T18',
            'status'        => 'required|in:upcoming,showing,stopped',
            'poster'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'trailer_url'   => 'nullable|url|max:255',
            'director'      => 'nullable|string|max:255',
            'country'       => 'nullable|string|max:100',
            'description'   => 'nullable|string',
            'genres'        => 'nullable|array',
            'genres.*'      => 'exists:genres,id',
            'actor_names'   => 'nullable|array',
            'actor_names.*' => 'nullable|string|max:255',
            // Định dạng chiếu: bắt buộc, là số nguyên và phải tồn tại trong bảng formats
            'format_id'     => 'required|integer|exists:formats,id',
        ], [
            'title.required'        => 'Tên phim không được để trống.',
            'duration.required'     => 'Thời lượng không được để trống.',
            'release_date.required' => 'Ngày khởi chiếu không được để trống.',
            'age_limit.required'    => 'Phân loại độ tuổi không được để trống.',
            'status.required'       => 'Trạng thái không được để trống.',
            'poster.image'          => 'File poster phải là hình ảnh.',
            'poster.mimes'          => 'Poster chỉ chấp nhận định dạng jpg, jpeg, png, webp.',
            'poster.max'            => 'Poster không được vượt quá 2MB.',
            'format_id.required'    => 'Vui lòng chọn một định dạng chiếu.',
            'format_id.integer'     => 'Định dạng chiếu không hợp lệ.',
            'format_id.exists'      => 'Định dạng chiếu không tồn tại.',
        ]);

        $posterPath = $movie->poster;
        if ($request->hasFile('poster')) {
            if ($movie->poster && str_starts_with($movie->poster, 'storage/')) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete(
                    str_replace('storage/', '', $movie->poster)
                );
            }
            $posterPath = 'storage/' . $request->file('poster')->store('posters', 'public');
        } elseif ($request->boolean('remove_poster')) {
            if ($movie->poster && str_starts_with($movie->poster, 'storage/')) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete(
                    str_replace('storage/', '', $movie->poster)
                );
            }
            $posterPath = null;
        }

        // Cập nhật slug khi title thay đổi
        $newSlug = $movie->slug;
        if ($movie->title !== $request->title) {
            $newSlug = $this->uniqueSlug($request->title, $movie->id);
        }

        $movie->update([
            'title'        => $request->title,
            'slug'         => $newSlug,
            'duration'     => $request->duration,
            'release_date' => $request->release_date,
            'age_limit'    => $request->age_limit,
            'status'       => $request->status,
            'poster'       => $posterPath,
            'trailer_url'  => $request->trailer_url,
            'director'     => $request->director,
            'country'      => $request->country,
            'description'  => $request->description,
            // Cập nhật format_id
            'format_id'    => $request->format_id,
        ]);

        $movie->genres()->sync($request->genres ?? []);

        // Bỏ qua việc sync với bảng pivot vì đã cập nhật format_id ở trên


        $this->syncActorsByName($movie, $request->actor_names ?? []);

        return redirect()->route('admin.movies.index')->with('success', 'Cập nhật phim thành công!');
    }

    public function destroy(Movie $movie)
    {
        if ($movie->showtimes()->exists()) {
            return redirect()->route('admin.movies.index')
                ->with('error', 'Không thể xóa phim «' . $movie->title . '» vì đang có suất chiếu hoạt động!');
        }

        $movie->delete();
        return redirect()->route('admin.movies.index')->with('success', 'Đã xóa phim «' . $movie->title . '»!');
    }

    public function restore($id)
    {
        $movie = Movie::withTrashed()->findOrFail($id);
        $movie->restore();
        return redirect()->route('admin.movies.index')->with('success', 'Đã khôi phục phim «' . $movie->title . '»!');
    }

    // Đồng bộ diễn viên theo tên — tạo mới nếu chưa có
    private function syncActorsByName(Movie $movie, array $names): void
    {
        $names = array_filter(array_map('trim', $names));
        $syncData = [];
        $roles = ['Nam chính', 'Nữ chính', 'Nam phụ', 'Nữ phụ', 'Diễn viên phụ'];

        foreach (array_values($names) as $i => $name) {
            $actor = Actor::firstOrCreate(['name' => $name], [
                'biography'    => null,
                'date_of_birth'=> null,
                'avatar'       => null,
            ]);
            $syncData[$actor->id] = ['role_name' => $roles[$i] ?? 'Diễn viên phụ'];
        }

        $movie->actors()->sync($syncData);
    }

    private function uniqueSlug(string $title, ?int $excludeId = null): string
    {
        $slug      = Str::slug($title);
        $baseSlug  = $slug;
        $counter   = 1;

        // Kiểm tra slug đã tồn tại chưa (bỏ qua chính bộ phim đang sửa)
        while (
            Movie::withTrashed()
                ->where('slug', $slug)
                ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
