@extends('layouts.customer')

@section('title', $movie->title . ' - FilmGo')

@section('content')
    <div class="bg-slate-50 w-full min-h-screen font-sans text-slate-850 antialiased py-12 selection:bg-brand-primary selection:text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Back Button & Flash Alerts -->
            <div class="mb-8 space-y-4">
                <a href="{{ route('movies.showing') }}" class="inline-flex items-center gap-2 text-xs font-black uppercase tracking-widest text-slate-400 hover:text-brand-primary transition-colors">
                    <span class="material-symbols-outlined text-base">arrow_back</span>
                    Quay lại danh sách phim
                </a>

                @if(session('error'))
                    <div class="bg-red-50 border-l-4 border-brand-primary p-4 text-red-700 text-sm font-medium flex items-center gap-3 rounded-none">
                        <span class="material-symbols-outlined text-red-500 text-xl shrink-0">error</span>
                        <div>{{ session('error') }}</div>
                    </div>
                @endif
            </div>

            <!-- Movie Details Card -->
            <div class="bg-white border border-slate-200 rounded-none shadow-sm overflow-hidden mb-12">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 p-6 md:p-8">
                    <!-- Poster Column -->
                    <div class="col-span-1">
                        <div class="relative aspect-[2/3] bg-slate-100 rounded-none overflow-hidden border border-slate-200 shadow-sm">
                            <img src="{{ $movie->poster ? asset('storage/' . $movie->poster) : asset('images/no-image.jpg') }}"
                                 alt="{{ $movie->title }}"
                                 class="w-full h-full object-cover">
                            <div class="absolute top-4 left-4 px-2.5 py-1 text-[9px] font-black bg-brand-primary text-white rounded-none border border-brand-primary/20 shadow-sm uppercase tracking-wider">
                                {{ $movie->age_limit }}
                            </div>
                        </div>
                    </div>

                    <!-- Details Column -->
                    <div class="col-span-1 md:col-span-2 flex flex-col justify-between space-y-6">
                        <div>
                            <div class="flex flex-wrap gap-2 mb-4">
                                @foreach($movie->genres as $genre)
                                    <span class="px-2.5 py-1 text-[10px] font-bold text-brand-primary bg-brand-primary/5 border border-brand-primary/10 rounded-none uppercase tracking-widest">
                                        {{ $genre->name }}
                                    </span>
                                @endforeach
                            </div>
                            <h1 class="text-3xl font-black text-slate-900 tracking-tighter uppercase mb-4 sm:text-4xl">
                                {{ $movie->title }}
                            </h1>
                            <p class="text-slate-500 leading-relaxed text-sm md:text-base font-medium mb-6">
                                {{ $movie->description }}
                            </p>
                            
                            <div class="grid grid-cols-2 gap-4 border-t border-slate-150 pt-6">
                                <div>
                                    <span class="block text-xs text-slate-400 font-black uppercase tracking-widest mb-1">Đạo diễn</span>
                                    <span class="text-sm font-bold text-slate-700">{{ $movie->director }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-slate-400 font-black uppercase tracking-widest mb-1">Thời lượng</span>
                                    <span class="text-sm font-bold text-slate-700 flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm text-slate-400">schedule</span>
                                        {{ $movie->duration }} phút
                                    </span>
                                </div>
                                <div>
                                    <span class="block text-xs text-slate-400 font-black uppercase tracking-widest mb-1">Quốc gia</span>
                                    <span class="text-sm font-bold text-slate-700">{{ $movie->country }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-slate-400 font-black uppercase tracking-widest mb-1">Khởi chiếu</span>
                                    <span class="text-sm font-bold text-slate-700">
                                        {{ $movie->release_date ? $movie->release_date->format('d/m/Y') : 'N/A' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Actors -->
                        @if($movie->actors->count() > 0)
                            <div class="border-t border-slate-150 pt-6">
                                <span class="block text-xs text-slate-400 font-black uppercase tracking-widest mb-3">Diễn viên</span>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($movie->actors as $actor)
                                        <span class="px-3 py-1.5 text-xs font-semibold text-slate-650 bg-slate-50 rounded-none border border-slate-200">
                                            {{ $actor->name }}
                                            @if($actor->pivot && $actor->pivot->role_name)
                                                <span class="text-slate-400 text-[10px] font-medium">({{ $actor->pivot->role_name }})</span>
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
            <div class="bg-white border border-slate-200 rounded-none shadow-sm p-6 md:p-8">
                <div class="border-b border-slate-150 pb-4 mb-6">
                    <h2 class="text-xl font-black text-slate-900 uppercase tracking-tighter flex items-center gap-2">
                        <span class="material-symbols-outlined text-brand-primary">calendar_month</span>
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
                                    class="date-tab-btn flex-shrink-0 flex flex-col items-center px-6 py-3 rounded-none border transition-all duration-200 {{ $isFirst ? 'bg-brand-primary border-brand-primary text-white shadow-md shadow-brand-primary/20' : 'bg-white border-slate-200 text-slate-600 hover:border-brand-primary hover:text-brand-primary' }}"
                                    data-date="{{ $date }}">
                                <span class="text-xs font-bold opacity-80 mb-1 uppercase tracking-widest">{{ $dayOfWeek }}</span>
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
                                <div class="border border-slate-200 rounded-none p-5 bg-slate-50/50">
                                    <div class="flex items-center gap-2 mb-4">
                                        <span class="material-symbols-outlined text-brand-primary text-xl">location_on</span>
                                        <h3 class="text-base font-bold text-slate-800 uppercase tracking-tight">{{ $cinemaName }}</h3>
                                    </div>
                                    <div class="flex flex-wrap gap-3">
                                        @foreach($cinemaShowtimes as $showtime)
                                            @if($showtime->is_bookable)
                                                <a href="{{ route('booking.select-seats', $showtime->id) }}" 
                                                   class="group bg-white border border-slate-200 px-5 py-3.5 rounded-none text-center transition-all duration-200 hover:border-brand-primary hover:shadow-md flex flex-col items-center justify-between min-w-[120px]">
                                                    <span class="block text-base font-black text-slate-800 group-hover:text-brand-primary">
                                                        {{ \Carbon\Carbon::parse($showtime->start_time)->format('H:i') }}
                                                    </span>
                                                    <span class="block text-[10px] text-slate-400 font-bold mt-1 uppercase tracking-wider">
                                                        {{ $showtime->room->room_name }} ({{ $showtime->room->room_type }})
                                                    </span>
                                                    <span class="mt-2 text-[10px] font-bold px-2.5 py-0.5 rounded-none bg-brand-primary/10 text-brand-primary border border-brand-primary/20 uppercase tracking-wider group-hover:bg-brand-primary group-hover:text-white transition-colors">
                                                        Đặt vé
                                                    </span>
                                                </a>
                                            @else
                                                <div class="bg-slate-100/90 border border-slate-200 px-5 py-3.5 rounded-none text-center opacity-85 cursor-not-allowed flex flex-col items-center justify-between min-w-[120px]">
                                                    <span class="block text-base font-black text-slate-500">
                                                        {{ \Carbon\Carbon::parse($showtime->start_time)->format('H:i') }}
                                                    </span>
                                                    <span class="block text-[10px] text-slate-400 font-bold mt-1 uppercase tracking-wider">
                                                        {{ $showtime->room->room_name }} ({{ $showtime->room->room_type }})
                                                    </span>
                                                    <span class="mt-2 text-[10px] font-bold px-2 py-0.5 rounded-none {{ $showtime->badge_class }} border uppercase tracking-wider">
                                                        {{ $showtime->status_label }}
                                                    </span>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @php $isFirst = false; @endphp
                    @endforeach
                @else
                    <!-- Empty State -->
                    <div class="text-center py-12 bg-slate-50 border border-dashed border-slate-200 rounded-none">
                        <div class="w-16 h-16 bg-white border border-slate-200 rounded-none flex items-center justify-center mx-auto mb-4 text-slate-350">
                            <span class="material-symbols-outlined text-3xl">event_busy</span>
                        </div>
                        <h3 class="text-sm font-bold text-slate-850 mb-1">Hiện không có suất chiếu</h3>
                        <p class="text-xs text-slate-400">Rạp chưa lên lịch chiếu cho bộ phim này trong những ngày tới. Hãy quay lại sau nhé!</p>
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
                btn.className = 'date-tab-btn flex-shrink-0 flex flex-col items-center px-6 py-3 rounded-none border transition-all duration-200 bg-white border-slate-200 text-slate-600 hover:border-brand-primary hover:text-brand-primary';
            });

            // Highlight the active tab
            const activeTab = document.querySelector(`.date-tab-btn[data-date="${date}"]`);
            if (activeTab) {
                activeTab.className = 'date-tab-btn flex-shrink-0 flex flex-col items-center px-6 py-3 rounded-none border transition-all duration-200 bg-brand-primary border-brand-primary text-white shadow-lg shadow-brand-primary/20';
            }
        }
    </script>
@endsection
