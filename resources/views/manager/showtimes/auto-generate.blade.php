@extends('layouts.manager')

@section('title', 'Xếp Lịch Chiếu Tự Động - FilmGo')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">
    <!-- Header -->
    <div class="flex justify-between items-center border-b border-slate-200 pb-4">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900 uppercase">Xếp Lịch Chiếu Tự Động</h2>
            <p class="text-sm text-slate-500 mt-1">Cấu hình ca làm việc và tự động sinh lịch chiếu tối ưu nhất.</p>
        </div>
        <a href="{{ route('manager.showtimes.index') }}"
           class="inline-flex items-center gap-1.5 px-4 py-2 border border-slate-300 text-slate-700 bg-white hover:bg-slate-50 font-semibold text-sm rounded-none transition-colors">
            <span class="material-symbols-outlined text-sm">arrow_back</span> Quay lại
        </a>
    </div>

    <!-- Vue App Mount Point -->
    <div id="auto-generate-app">
        <!-- Render component -->
        <auto-generate-showtime-form
            my-cinemas-url="{{ route('manager.api.my-cinemas') }}"
            rooms-url-pattern="{{ route('manager.api.rooms-by-cinema', ['cinema_id' => ':cinema_id']) }}"
            auto-generate-url="{{ route('manager.showtimes.api.auto-generate') }}"
            cancel-url="{{ route('manager.showtimes.index') }}"
        ></auto-generate-showtime-form>
    </div>
</div>

<!-- Preload data from PHP into JS variables BEFORE Vue loads -->
<script>
    window.__SHOWTIME_DATA__ = {
        movies: @json($movies),
        csrfToken: "{{ csrf_token() }}"
    };
</script>

<!-- Vue 3 CDN -->
<script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
<!-- Axios CDN -->
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<!-- Register component and Mount App -->
<script>
    const { createApp } = Vue;
    // Import Vue component template and setup from script
</script>
<script src="{{ asset('js/auto-generate-showtime.js') }}"></script>
@endsection
