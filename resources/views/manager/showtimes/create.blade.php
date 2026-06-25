@extends('layouts.manager')

@section('title', 'Tạo Suất Chiếu Mới - FilmGo')

@section('content')
<div class="space-y-6 max-w-2xl mx-auto">
    <!-- Header -->
    <div class="flex justify-between items-center border-b border-slate-200 pb-4">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900 uppercase">Tạo Suất Chiếu Mới</h2>
            <p class="text-sm text-slate-500 mt-1">Lên lịch chiếu phim và cấu hình giá vé cơ bản.</p>
        </div>
        <a href="{{ route('manager.showtimes.index') }}"
           class="inline-flex items-center gap-1.5 px-4 py-2 border border-slate-300 text-slate-700 bg-white hover:bg-slate-50 font-semibold text-sm rounded-none transition-colors">
            <span class="material-symbols-outlined text-sm">arrow_back</span> Quay lại
        </a>
    </div>

    <!-- Vue App Mount Point — template is fully in the separate <template> tag below -->
    <div id="showtime-app"></div>
</div>

<!-- Preload data from PHP into JS variables BEFORE Vue loads -->
<script>
    window.__SHOWTIME_DATA__ = {
        movies:       @json($movies),
        csrfToken:    "{{ csrf_token() }}",
        urls: {
            myCinemas:     "{{ route('api.admin.my-cinemas') }}",
            roomsByCinema: "{{ route('api.admin.cinemas.rooms', ['cinema_id' => ':cinema_id']) }}",
            checkOverlap:  "{{ route('manager.showtimes.api.check-overlap') }}",
            suggestPrice:  "{{ route('manager.showtimes.api.suggest-price') }}",
            store:         "{{ route('manager.showtimes.api.store') }}",
            redirect:      "{{ route('manager.showtimes.index') }}",
            cancel:        "{{ route('manager.showtimes.index') }}"
        }
    };
</script>

<!-- Vue 3 CDN -->
<script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
<!-- Axios CDN -->
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<!-- Vue app logic — no Blade syntax here, purely JS -->
<script src="{{ asset('js/create-showtime.js') }}"></script>
@endsection
