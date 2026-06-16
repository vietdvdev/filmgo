<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use Illuminate\Http\Request;

class GenreController extends Controller
{
    public function index(Request $request)
    {
        $query = Genre::withCount('movies');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $genres = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        return view('admin.genres.index', compact('genres'));
    }

    public function create()
    {
        return view('admin.genres.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100|unique:genres,name',
            'description' => 'nullable|string',
        ], [
            'name.required' => 'Tên thể loại không được để trống.',
            'name.unique'   => 'Tên thể loại này đã tồn tại.',
            'name.max'      => 'Tên thể loại không được vượt quá 100 ký tự.',
        ]);

        Genre::create($request->only('name', 'description'));

        return redirect()->route('admin.genres.index')->with('success', 'Thêm thể loại thành công!');
    }

    public function edit(Genre $genre)
    {
        return view('admin.genres.edit', compact('genre'));
    }

    public function update(Request $request, Genre $genre)
    {
        $request->validate([
            'name'        => 'required|string|max:100|unique:genres,name,' . $genre->id,
            'description' => 'nullable|string',
        ], [
            'name.required' => 'Tên thể loại không được để trống.',
            'name.unique'   => 'Tên thể loại này đã tồn tại.',
            'name.max'      => 'Tên thể loại không được vượt quá 100 ký tự.',
        ]);

        $genre->update($request->only('name', 'description'));

        return redirect()->route('admin.genres.index')->with('success', 'Cập nhật thể loại thành công!');
    }

    public function destroy(Genre $genre)
    {
        if ($genre->movies()->count() > 0) {
            return redirect()->route('admin.genres.index')->with('error', 'Không thể xóa thể loại đang có phim liên kết!');
        }

        $genre->delete();

        return redirect()->route('admin.genres.index')->with('success', 'Xóa thể loại thành công!');
    }
}
