@extends('layouts.customer')

@section('title', $movie->title . ' - FilmGo')

@section('content')
    <div class="bg-neutral-50 w-full min-h-screen font-sans text-neutral-800 antialiased py-12 selection:bg-indigo-500 selection:text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Back Button -->
            <div class="mb-8">
                <a href="{{ route('movies.showing') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-neutral-500 hover:text-indigo-600 transition-colors">
                    <span class="material-symbols-outlined text-lg">arrow_back</span>
                    Quay lại danh sách phim
                </a>
            </div>

            <!-- Movie Details Card -->
            <div class="bg-white rounded-[32px] border border-slate-200/60 shadow-sm overflow-hidden mb-12">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 p-6 md:p-8">
                    <!-- Poster Column -->
                    <div class="col-span-1">
                        <div class="relative aspect-[2/3] bg-slate-100 rounded-2xl overflow-hidden border border-slate-200/50 shadow-md">
                            <img src="{{ $movie->poster ? asset('storage/' . $movie->poster) : asset('images/no-image.jpg') }}"
                                 alt="{{ $movie->title }}"
                                 class="w-full h-full object-cover">
                            <div class="absolute top-4 left-4 px-3 py-1 text-xs font-black bg-indigo-600 text-white rounded-lg shadow-sm">
                                {{ $movie->age_limit }}
                            </div>
                        </div>
                    </div>

                    <!-- Details Column -->
                    <div class="col-span-1 md:col-span-2 flex flex-col justify-between space-y-6">
                        <div>
                            <div class="flex flex-wrap gap-2 mb-3">
                                @foreach($movie->genres as $genre)
                                    <span class="px-3 py-1 text-xs font-bold text-indigo-600 bg-indigo-50 rounded-lg">
                                        {{ $genre->name }}
                                    </span>
                                @endforeach
                            </div>
                            <h1 class="text-3xl font-black text-neutral-900 tracking-tight uppercase mb-4">
                                {{ $movie->title }}
                            </h1>
                            <p class="text-neutral-500 leading-relaxed text-sm md:text-base font-medium mb-6">
                                {{ $movie->description }}
                            </p>
                            
                            <div class="grid grid-cols-2 gap-4 border-t border-slate-100 pt-6">
                                <div>
                                    <span class="block text-xs text-neutral-400 font-semibold uppercase tracking-wider mb-1">Đạo diễn</span>
                                    <span class="text-sm font-bold text-neutral-800">{{ $movie->director }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-neutral-400 font-semibold uppercase tracking-wider mb-1">Thời lượng</span>
                                    <span class="text-sm font-bold text-neutral-800 flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm text-neutral-400">schedule</span>
                                        {{ $movie->duration }} phút
                                    </span>
                                </div>
                                <div>
                                    <span class="block text-xs text-neutral-400 font-semibold uppercase tracking-wider mb-1">Quốc gia</span>
                                    <span class="text-sm font-bold text-neutral-800">{{ $movie->country }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-neutral-400 font-semibold uppercase tracking-wider mb-1">Khởi chiếu</span>
                                    <span class="text-sm font-bold text-neutral-800">
                                        {{ $movie->release_date ? $movie->release_date->format('d/m/Y') : 'N/A' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Actors -->
                        @if($movie->actors->count() > 0)
                            <div class="border-t border-slate-100 pt-6">
                                <span class="block text-xs text-neutral-400 font-semibold uppercase tracking-wider mb-3">Diễn viên</span>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($movie->actors as $actor)
                                        <span class="px-3 py-1.5 text-xs font-semibold text-neutral-600 bg-neutral-100 rounded-xl border border-slate-200/40">
                                            {{ $actor->name }}
                                            @if($actor->pivot && $actor->pivot->role_name)
                                                <span class="text-neutral-400 text-[10px] font-medium">({{ $actor->pivot->role_name }})</span>
                                            @endif
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Showtime List Section -->
            <div class="bg-white rounded-[32px] border border-slate-200/60 shadow-sm p-6 md:p-8">
                <div class="border-b border-slate-100 pb-4 mb-6">
                    <h2 class="text-xl font-bold text-neutral-900 uppercase tracking-tight flex items-center gap-2">
                        <span class="material-symbols-outlined text-indigo-600">calendar_month</span>
                        Lịch chiếu & Đặt vé
                    </h2>
                </div>

                @if($showtimesGrouped->count() > 0)
                    <!-- Date Selector Tab -->
                    <div class="flex gap-3 overflow-x-auto pb-4 mb-8 scrollbar-none" id="dateTabs">
                        @php $isFirst = true; @endphp
                        @foreach($showtimesGrouped as $date => $cinemas)
                            @php
                                $dateObj = \Carbon\Carbon::parse($date);
                                $dayOfWeek = $dateObj->isToday() ? 'Hôm nay' : ($dateObj->isTomorrow() ? 'Ngày mai' : 'Thứ ' . ($dateObj->dayOfWeek == 0 ? 'Chủ Nhật' : $dateObj->dayOfWeek + 1));
                            @endphp
                            <button onclick="switchDate('{{ $date }}')" 
                                    class="date-tab-btn flex-shrink-0 flex flex-col items-center px-5 py-3 rounded-2xl border transition-all duration-200 {{ $isFirst ? 'bg-indigo-600 border-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'bg-white border-slate-200 text-neutral-700 hover:border-indigo-600 hover:text-indigo-600' }}"
                                    data-date="{{ $date }}">
                                <span class="text-xs font-medium opacity-80 mb-1">{{ $dayOfWeek }}</span>
                                <span class="text-lg font-black tracking-tight">{{ $dateObj->format('d/m') }}</span>
                            </button>
                            @php $isFirst = false; @endphp
                        @endforeach
                    </div>

                    <!-- Showtimes Content Grouped by Date -->
                    @php $isFirst = true; @endphp
                    @foreach($showtimesGrouped as $date => $cinemas)
                        <div class="date-content-panel space-y-6 {{ $isFirst ? 'block' : 'hidden' }}" id="panel-{{ $date }}">
                            @foreach($cinemas as $cinemaName => $cinemaShowtimes)
                                <div class="border border-slate-150 rounded-2xl p-5 bg-neutral-50/50">
                                    <div class="flex items-center gap-2 mb-4">
                                        <span class="material-symbols-outlined text-indigo-600 text-xl">location_on</span>
                                        <h3 class="text-base font-bold text-neutral-800">{{ $cinemaName }}</h3>
                                    </div>
                                    <div class="flex flex-wrap gap-3">
                                        @foreach($cinemaShowtimes as $showtime)
                                            <a href="{{ route('booking.select-seats', $showtime->id) }}" 
                                               class="group bg-white border border-slate-200 px-5 py-3.5 rounded-xl text-center transition-all duration-200 hover:border-indigo-600 hover:shadow-md">
                                                <span class="block text-base font-black text-neutral-800 group-hover:text-indigo-600">
                                                    {{ \Carbon\Carbon::parse($showtime->start_time)->format('H:i') }}
                                                </span>
                                                <span class="block text-[10px] text-neutral-400 font-bold mt-1 uppercase">
                                                    {{ $showtime->room->room_name }} ({{ $showtime->room->room_type }})
                                                </span>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @php $isFirst = false; @endphp
                    @endforeach
                @else
                    <!-- Empty State -->
                    <div class="text-center py-12">
                        <div class="w-16 h-16 bg-neutral-100 rounded-2xl flex items-center justify-center mx-auto mb-4 text-neutral-400">
                            <span class="material-symbols-outlined text-3xl">event_busy</span>
                        </div>
                        <h3 class="text-sm font-bold text-neutral-800 mb-1">Hiện không có suất chiếu</h3>
                        <p class="text-xs text-neutral-400">Rạp chưa lên lịch chiếu cho bộ phim này trong những ngày tới. Hãy quay lại sau nhé!</p>
                    </div>
                @endif
            </div>

        </div>
    </div>

    <!-- Simple JavaScript for Tab Switching -->
    <script>
        function switchDate(date) {
            // Hide all panels
            document.querySelectorAll('.date-content-panel').forEach(panel => {
                panel.classList.add('hidden');
                panel.classList.remove('block');
            });
            
            // Show the selected panel
            const targetPanel = document.getElementById('panel-' + date);
            if (targetPanel) {
                targetPanel.classList.remove('hidden');
                targetPanel.classList.add('block');
            }

            // Reset tab styles
            document.querySelectorAll('.date-tab-btn').forEach(btn => {
                btn.className = 'date-tab-btn flex-shrink-0 flex flex-col items-center px-5 py-3 rounded-2xl border transition-all duration-200 bg-white border-slate-200 text-neutral-700 hover:border-indigo-600 hover:text-indigo-600';
            });

            // Highlight the active tab
            const activeTab = document.querySelector(`.date-tab-btn[data-date="${date}"]`);
            if (activeTab) {
                activeTab.className = 'date-tab-btn flex-shrink-0 flex flex-col items-center px-5 py-3 rounded-2xl border transition-all duration-200 bg-indigo-600 border-indigo-600 text-white shadow-lg shadow-indigo-600/20';
            }
        }
    </script>
@endsection
