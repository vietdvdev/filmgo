@extends('layouts.customer')

@section('title', 'Đặt Vé Thành Công - FilmGo')

@section('content')
    <div class="bg-neutral-50 w-full min-h-screen font-sans text-neutral-800 antialiased py-12 selection:bg-indigo-500 selection:text-white">
        <div class="max-w-3xl mx-auto px-4 sm:px-6">

            <div class="bg-white rounded-[32px] border border-slate-200/60 shadow-sm overflow-hidden p-6 md:p-10 text-center">
                    @if(session('success'))
                        <div class="mb-4 rounded-3xl bg-emerald-50 border border-emerald-100 text-emerald-700 px-5 py-4 text-sm font-semibold">
                            {{ session('success') }}
                        </div>
                    @endif
                
                    <!-- Success icon animation wrapper -->
                    <div class="w-20 h-20 bg-emerald-50 border border-emerald-100 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner animate-bounce">
                        <span class="material-symbols-outlined text-4xl">check_circle</span>
                    </div>
                <h1 class="text-2xl md:text-3xl font-black text-neutral-900 uppercase tracking-tight mb-2">Đặt Vé Thành Công!</h1>
                <p class="text-sm text-neutral-400 font-medium max-w-md mx-auto mb-8">Cảm ơn bạn đã lựa chọn FilmGo. Đơn hàng đặt vé của bạn đã được ghi nhận thành công trong hệ thống.</p>

                <!-- Ticket Booking Code Info Card -->
                <div class="bg-neutral-50 border border-slate-150 rounded-3xl p-6 mb-8 text-left space-y-4">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b border-slate-200/60 pb-4 gap-2">
                        <div>
                            <span class="block text-[10px] text-neutral-400 font-bold uppercase tracking-wider mb-0.5">Mã Tra Cứu Đơn</span>
                            <span class="text-xl font-black text-indigo-600 tracking-wider">{{ $booking->booking_code }}</span>
                        </div>
                        <div class="text-left sm:text-right">
                            <span class="block text-[10px] text-neutral-400 font-bold uppercase tracking-wider mb-0.5">Hạn Chót Thanh Toán / Nhận Vé</span>
                            <span class="text-xs font-bold text-neutral-800">{{ $booking->expired_at ? \Carbon\Carbon::parse($booking->expired_at)->format('H:i d/m/Y') : 'N/A' }}</span>
                        </div>
                    </div>

                    <!-- Details information table -->
                    @if($booking->showtime)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div>
                            <span class="block text-neutral-400 font-semibold mb-0.5">Phim Điện Ảnh</span>
                            <span class="font-extrabold text-neutral-800 uppercase leading-snug">{{ $booking->showtime->movie->title ?? 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="block text-neutral-400 font-semibold mb-0.5">Rạp Chiếu / Phòng</span>
                            <span class="font-bold text-neutral-800">{{ $booking->showtime->room->cinema->name ?? '' }} - {{ $booking->showtime->room->room_name ?? '' }}</span>
                        </div>
                        <div>
                            <span class="block text-neutral-400 font-semibold mb-0.5">Thời Gian Chiếu</span>
                            <span class="font-bold text-indigo-600">{{ $booking->showtime->start_time ? \Carbon\Carbon::parse($booking->showtime->start_time)->format('H:i') : '' }} | {{ $booking->showtime->show_date ? $booking->showtime->show_date->format('d/m/Y') : '' }}</span>
                        </div>
                        <div>
                            <span class="block text-neutral-400 font-semibold mb-0.5">Vị Trí Ghế</span>
                            <span class="font-bold text-neutral-800">
                                @foreach($booking->bookingDetails as $detail)
                                    <span class="inline-block bg-indigo-50 border border-indigo-100 text-indigo-700 px-1.5 py-0.5 rounded font-black mr-1 mb-1">{{ $detail->showtimeSeat->seat->seat_row ?? '' }}{{ $detail->showtimeSeat->seat->seat_number ?? '' }}</span>
                                @endforeach
                            </span>
                        </div>
                    </div>
                    @endif

                    <!-- Combos booked summary -->
                    @if($booking->combos->count() > 0)
                        <div class="border-t border-slate-200/60 pt-4">
                            <span class="block text-[10px] text-neutral-400 font-bold uppercase tracking-wider mb-2">Bắp Nước Đặt Kèm</span>
                            <div class="flex flex-wrap gap-2">
                                @foreach($booking->combos as $combo)
                                    <span class="px-3 py-1.5 text-xs font-bold text-neutral-700 bg-white border border-slate-200 rounded-xl">
                                        {{ $combo->combo_name }} <span class="text-indigo-600 font-black">x{{ $combo->pivot->quantity }}</span>
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Grand Total amount -->
                    <div class="border-t border-slate-200/60 pt-4 flex justify-between items-center">
                        <span class="text-xs font-bold text-neutral-400">Tổng Số Tiền</span>
                        <span class="text-lg font-black text-neutral-900">{{ number_format($booking->total_amount) }}đ</span>
                    </div>
                </div>

                <!-- Ticket card section for mobile viewing -->
                <div class="mb-8">
                    <div class="mb-3 flex items-center justify-between">
                        <h4 class="text-xs font-black uppercase tracking-[0.3em] text-neutral-500">Vé điện tử của bạn</h4>
                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.25em] text-emerald-600">Đã thanh toán</span>
                    </div>
                    @include('customer.bookings.partials.ticket-card', ['booking' => $booking])
                </div>

                <!-- Guidance and Home Navigation -->
                <div class="bg-indigo-50/50 border border-indigo-100 rounded-2xl p-4 text-left mb-10">
                    <h4 class="text-xs font-black text-indigo-800 uppercase tracking-wider mb-1 flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">info</span> Hướng dẫn nhận vé tại quầy
                    </h4>
                    <p class="text-xs text-indigo-700/80 leading-relaxed font-semibold">Vui lòng xuất trình mã đơn đặt vé <span class="font-black text-indigo-900">{{ $booking->booking_code }}</span> cho nhân viên tại quầy vé của rạp để làm thủ tục nhận vé cứng và bắp nước trước suất chiếu ít nhất 15 phút.</p>
                </div>

                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('home') }}" class="px-8 py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-2xl shadow-md shadow-indigo-600/20 text-sm uppercase tracking-wider transition-all duration-200 flex items-center justify-center gap-1.5">
                        <span class="material-symbols-outlined text-sm">home</span>
                        Quay Về Trang Chủ
                    </a>
                </div>

            </div>

        </div>
    </div>
@endsection
