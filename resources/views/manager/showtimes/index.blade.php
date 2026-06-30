@extends('layouts.manager')

@section('title', 'Lịch Suất Chiếu - FilmGo')

@section('content')
<div id="app" class="space-y-6">
    {{-- Render Vue component ShowtimeList --}}
    <showtime-list
        :cinemas="{{ json_encode($cinemas) }}"
        :movies="{{ json_encode($movies) }}"
        create-url="{{ route('manager.showtimes.create') }}"
        auto-generate-url="{{ route('manager.showtimes.auto-generate.view') }}"
        api-rooms-url="{{ route('api.manager.rooms') }}"
        api-showtimes-url="{{ route('api.manager.showtimes') }}"
        api-bulk-open-sales-url="{{ route('api.manager.showtimes.bulk-open-sales') }}"
        api-delete-showtime-url="/api/manager/showtimes"
    ></showtime-list>
</div>
@endsection

@section('scripts')
    {{-- Nạp bundle js chứa Vue app và component --}}
    @vite(['resources/js/app.js'])
@endsection

