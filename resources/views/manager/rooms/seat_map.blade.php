@extends('layouts.manager')

@section('title', 'Sơ Đồ Ghế - FilmGo')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center border-b border-slate-200 pb-4">
        <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Phòng Chiếu: {{ $room->room_name }}</span>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900 uppercase mt-0.5">Sơ Đồ Thiết Kế Ghế</h2>
        </div>
        <a href="{{ route('manager.rooms.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 border border-slate-300 text-slate-700 bg-white hover:bg-slate-50 font-semibold text-sm rounded-none transition-colors">
            <span class="material-symbols-outlined text-sm">arrow_back</span> Quay lại
        </a>
    </div>

    <!-- Seat Layout Map Container -->
    <div class="bg-white border border-slate-200 shadow-sm p-8 rounded-none space-y-12">
        <!-- Screen Visualizer -->
        <div class="flex flex-col items-center">
            <div class="w-full max-w-xl h-2.5 bg-slate-400 shadow-[0_4px_20px_rgba(100,116,139,0.3)]"></div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-2">Màn Hình Chiếu</p>
        </div>

        <!-- Seat Grid Stub -->
        <div class="flex flex-col items-center space-y-3 overflow-x-auto py-4">
            @php
                $rows = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K'];
                $seatCountPerRow = 12;
            @endphp
            
            @foreach($rows as $row)
                <div class="flex items-center gap-2 flex-nowrap">
                    <!-- Row Name Label Left -->
                    <span class="w-6 text-sm font-black text-slate-400 text-center">{{ $row }}</span>
                    
                    <!-- Seats -->
                    @for($i = 1; $i <= $seatCountPerRow; $i++)
                        @php
                            // Phân biệt màu loại ghế giả lập
                            $seatBg = 'bg-slate-100 hover:bg-slate-200 border-slate-300 text-slate-600';
                            if ($row === 'F' || $row === 'G' || $row === 'H') {
                                // Ghế VIP
                                $seatBg = 'bg-amber-50 hover:bg-amber-100 border-amber-300 text-amber-800';
                            } elseif ($row === 'K') {
                                // Ghế Double/Sweetbox
                                $seatBg = 'bg-pink-50 hover:bg-pink-100 border-pink-300 text-pink-800 w-16';
                            }
                        @endphp
                        
                        <div class="h-8 w-8 {{ $row === 'K' && $i % 2 !== 0 ? 'w-16' : 'w-8' }} border text-[10px] font-bold flex items-center justify-center cursor-pointer select-none rounded-none transition-all {{ $seatBg }}" title="Ghế {{ $row . $i }}">
                            {{ $row . $i }}
                        </div>
                    @endfor

                    <!-- Row Name Label Right -->
                    <span class="w-6 text-sm font-black text-slate-400 text-center">{{ $row }}</span>
                </div>
            @endforeach
        </div>

        <!-- Legend -->
        <div class="flex flex-wrap justify-center gap-8 border-t border-slate-100 pt-6">
            <div class="flex items-center gap-2">
                <div class="h-6 w-6 bg-slate-100 border border-slate-300 rounded-none"></div>
                <span class="text-xs font-semibold text-slate-600">Ghế Thường (Standard)</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="h-6 w-6 bg-amber-50 border border-amber-300 rounded-none"></div>
                <span class="text-xs font-semibold text-slate-600">Ghế VIP (Comfort)</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="h-6 w-6 bg-pink-50 border border-pink-300 rounded-none"></div>
                <span class="text-xs font-semibold text-slate-600">Ghế Đôi (Sweetbox)</span>
            </div>
        </div>
    </div>
</div>
@endsection
