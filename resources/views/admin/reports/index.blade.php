@extends('layouts.admin')

@section('title', 'Báo Cáo Doanh Thu - FilmGo Admin')

@section('content')
<div class="flex-grow overflow-y-auto bg-[#f8f9fb] text-zinc-700 p-6 md:p-8">
    <div class="border-b border-zinc-200 pb-6 mb-8 mt-6 md:mt-10">
        <h1 class="text-2xl md:text-3xl font-black uppercase tracking-tight text-zinc-900 flex items-center gap-2">
            <span class="material-symbols-outlined text-red-600 text-3xl">bar_chart</span>
            Báo Cáo Doanh Thu
        </h1>
        <p class="text-xs md:text-sm text-zinc-500 font-medium mt-1">Thống kê và phân tích doanh thu hệ thống rạp chiếu phim</p>
    </div>

    <div class="bg-white border border-zinc-200/80 rounded-3xl p-12 shadow-sm flex flex-col items-center justify-center text-center">
        <span class="material-symbols-outlined text-zinc-300 text-7xl mb-4">bar_chart</span>
        <p class="text-zinc-400 font-semibold text-sm">Tính năng báo cáo đang được phát triển.</p>
    </div>
</div>
@endsection
