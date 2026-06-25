@extends('layouts.manager')

@section('title', 'Rạp Của Tôi - FilmGo')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-slate-200 pb-5">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Rạp Đang Quản Lý</h1>
            <p class="text-sm text-slate-500 mt-1">Danh sách các rạp chiếu phim bạn được phân công quản lý.</p>
        </div>
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-100 text-blue-800 text-xs font-bold rounded-none">
            <span class="material-symbols-outlined text-sm">theaters</span>
            {{ $cinemas->count() }} rạp
        </span>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="flex items-center gap-3 p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 text-sm font-semibold">
            <span class="material-symbols-outlined text-emerald-600">check_circle</span>
            {{ session('success') }}
        </div>
    @endif

    {{-- Search --}}
    <div class="bg-white border border-slate-200 shadow-sm p-4">
        <form method="GET" action="{{ route('manager.cinemas.index') }}" class="flex gap-2">
            <div class="relative w-72">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Tìm theo tên rạp, thành phố..."
                    class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-300 text-sm text-slate-900 focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 rounded-none">
            </div>
            <button type="submit" class="bg-slate-800 hover:bg-slate-700 text-white text-sm font-semibold px-4 py-2 rounded-none transition-colors">
                Tìm kiếm
            </button>
            @if(request('search'))
                <a href="{{ route('manager.cinemas.index') }}"
                    class="bg-slate-200 text-slate-700 hover:bg-slate-300 text-sm font-semibold px-4 py-2 rounded-none transition-colors flex items-center">
                    Xóa lọc
                </a>
            @endif
        </form>
    </div>

    {{-- Cinema Cards --}}
    @forelse($cinemas as $cinema)
        <div class="bg-white border border-slate-200 shadow-sm overflow-hidden">

            {{-- Card Header --}}
            <div class="flex items-center justify-between px-6 py-4 bg-slate-800 text-white">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-blue-400 text-2xl">theaters</span>
                    <div>
                        <h3 class="text-base font-bold tracking-tight">{{ $cinema->name }}</h3>
                        <span class="text-xs text-slate-400 font-medium">ID #{{ $cinema->id }}</span>
                    </div>
                </div>
                @if($cinema->status === 'active')
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 inline-block"></span>
                        Đang hoạt động
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold bg-red-500/20 text-red-300 border border-red-500/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-red-400 inline-block"></span>
                        Ngừng hoạt động
                    </span>
                @endif
            </div>

            {{-- Card Body --}}
            <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">

                {{-- Địa chỉ --}}
                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-slate-400 mt-0.5">location_on</span>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-0.5">Địa Chỉ</p>
                        <p class="text-sm text-slate-800 font-medium leading-relaxed">{{ $cinema->address }}</p>
                    </div>
                </div>

                {{-- Thành phố --}}
                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-slate-400 mt-0.5">apartment</span>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-0.5">Thành Phố</p>
                        <p class="text-sm text-slate-800 font-medium">{{ $cinema->city }}</p>
                    </div>
                </div>

                {{-- Điện thoại --}}
                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-slate-400 mt-0.5">phone</span>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-0.5">Điện Thoại</p>
                        <p class="text-sm text-slate-800 font-medium">{{ $cinema->phone }}</p>
                    </div>
                </div>

            </div>

            {{-- Card Footer: Stats + Actions --}}
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">

                {{-- Stats --}}
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-1.5 text-sm font-semibold text-slate-600">
                        <span class="material-symbols-outlined text-base text-slate-400">meeting_room</span>
                        {{ $cinema->rooms_count }} phòng chiếu
                    </div>
                </div>

                {{-- Quick Actions --}}
                <div class="flex items-center gap-2">
                    <a href="{{ route('manager.rooms.index') }}?cinema_id={{ $cinema->id }}"
                        class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 border border-blue-300 text-blue-700 bg-white hover:bg-blue-600 hover:text-white hover:border-blue-600 transition-all rounded-none">
                        <span class="material-symbols-outlined text-sm">meeting_room</span>
                        Phòng Chiếu
                    </a>
                    <a href="{{ route('manager.staff.index') }}?cinema_id={{ $cinema->id }}"
                        class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 border border-slate-300 text-slate-700 bg-white hover:bg-slate-800 hover:text-white hover:border-slate-800 transition-all rounded-none">
                        <span class="material-symbols-outlined text-sm">group</span>
                        Nhân Sự
                    </a>
                    <a href="{{ route('manager.showtimes.index') }}?cinema_id={{ $cinema->id }}"
                        class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 border border-slate-300 text-slate-700 bg-white hover:bg-slate-800 hover:text-white hover:border-slate-800 transition-all rounded-none">
                        <span class="material-symbols-outlined text-sm">schedule</span>
                        Suất Chiếu
                    </a>
                </div>

            </div>
        </div>
    @empty
        <div class="bg-white border border-slate-200 shadow-sm p-12 text-center">
            <span class="material-symbols-outlined text-5xl text-slate-300 mb-3 block">theaters</span>
            @if(auth()->user()->roles()->where('name', 'admin')->exists())
                <p class="text-base font-semibold text-slate-500">Bạn đang truy cập với vai trò Quản trị viên (Admin)</p>
                <p class="text-sm text-slate-400 mt-1 mb-6">Trang này chỉ hiển thị rạp được phân công. Hãy đi tới trang quản trị của Admin để thêm rạp và phân công.</p>
                <a href="{{ route('admin.cinemas.index') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm px-5 py-2.5 transition-colors">
                    <span class="material-symbols-outlined text-sm">settings</span> Quản lý rạp chiếu (Admin)
                </a>
            @else
                <p class="text-base font-semibold text-slate-500">Bạn chưa được phân công quản lý rạp nào.</p>
                <p class="text-sm text-slate-400 mt-1">Vui lòng liên hệ Admin để được phân công.</p>
            @endif
        </div>
    @endforelse

</div>
@endsection
