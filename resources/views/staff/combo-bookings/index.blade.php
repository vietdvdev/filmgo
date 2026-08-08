@extends('layouts.staff')

@section('title', 'Quản Lý Đặt Combo - FilmGo Staff')

@section('content')
<div class="p-6 md:p-8 space-y-6 max-w-7xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold text-orange-600 uppercase tracking-wider">
                    <span class="material-symbols-outlined">fastfood</span>
                    <span>Rạp: {{ $cinema->name ?? 'Phân công nhân viên' }}</span>
                </div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 mt-1">Quản Lý Đặt Combo</h1>
                <p class="text-sm text-gray-500 mt-0.5">Theo dõi các đơn combo/F&B do staff rạp tạo và in biên lai cho khách.</p>
            </div>

            <form method="GET" action="{{ route('staff.combo-bookings.index') }}" class="flex flex-col lg:flex-row lg:items-end gap-3 w-full">
                <div class="flex items-center gap-2 bg-gray-50 border border-gray-300 rounded-xl px-3.5 py-2">
                    <label for="date" class="text-xs font-semibold text-gray-600 whitespace-nowrap">Ngày tạo:</label>
                    <input type="date" id="date" name="date" value="{{ $selectedDate }}" class="bg-transparent text-sm font-medium text-gray-900 focus:outline-none" />
                </div>

                <div class="flex-1 min-w-[190px]">
                    <label for="booking_code" class="sr-only">Mã đơn</label>
                    <input type="text" id="booking_code" name="booking_code" value="{{ $filters['booking_code'] ?? '' }}" placeholder="Tìm mã đơn" class="w-full px-4 py-2 bg-gray-50 border border-gray-300 rounded-xl text-sm text-gray-900 placeholder:text-gray-500 focus:outline-none" />
                </div>

                

                <div class="flex items-center gap-2">
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold rounded-xl transition-all">
                        <span>Lọc dữ liệu</span>
                    </button>
                    @if($selectedDate !== now()->toDateString() || !empty($filters['booking_code']) || !empty($filters['print_status']))
                        <a href="{{ route('staff.combo-bookings.index') }}" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-xl transition-colors">Xóa lọc</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-xs font-bold uppercase tracking-wider border-b border-gray-200">
                        <th class="py-4 px-5">Mã đơn</th>
                        <th class="py-4 px-5">Khách hàng</th>
                        <th class="py-4 px-5">Sản phẩm</th>
                        <th class="py-4 px-5 text-center">Thanh toán</th>
                        <th class="py-4 px-5 text-center">Trạng thái</th>
                        <th class="py-4 px-5 text-center">In/Biên lai</th>
                        <th class="py-4 px-5 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-sm">
                    @forelse($bookings as $booking)
                        <tr class="hover:bg-orange-50/40 transition-colors">
                            <td class="py-4 px-5">
                                <span class="font-mono font-bold text-orange-600 text-base">{{ $booking->booking_code }}</span>
                                <div class="text-xs text-gray-400 mt-0.5">{{ $booking->created_at?->format('H:i - d/m/Y') }}</div>
                            </td>
                            <td class="py-4 px-5">
                                @if($booking->user)
                                    <p class="font-semibold text-gray-900">{{ $booking->user->full_name }}</p>
                                    @if($booking->user->phone)
                                        <p class="text-xs text-gray-500 mt-0.5">{{ $booking->user->phone }}</p>
                                    @endif
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 bg-gray-100 text-gray-600 font-medium rounded-lg text-xs">Khách vãng lai</span>
                                @endif
                            </td>
                            <td class="py-4 px-5">
                                <div class="space-y-1">
                                    @foreach($booking->combos as $combo)
                                        <div class="text-xs font-semibold text-gray-700">{{ $combo->pivot->quantity }}x {{ $combo->combo_name }}</div>
                                    @endforeach
                                    @if($booking->comboItems->isNotEmpty())
                                        @foreach($booking->comboItems as $item)
                                            <div class="text-xs text-gray-500">{{ $item->quantity }}x {{ $item->comboItem?->name }}</div>
                                        @endforeach
                                    @endif
                                    @if($booking->combos->isEmpty() && $booking->comboItems->isEmpty())
                                        <span class="text-xs text-gray-400 italic">Không có sản phẩm</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-4 px-5 text-center">
                                @php $pStatus = strtolower($booking->payment_status ?? 'pending'); @endphp
                                @if(in_array($pStatus, ['paid', 'completed']))
                                    <span class="inline-flex items-center px-3 py-1 bg-emerald-50 text-emerald-700 font-semibold rounded-full text-xs">Đã thanh toán</span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 bg-amber-50 text-amber-700 font-semibold rounded-full text-xs">Chờ thanh toán</span>
                                @endif
                            </td>
                            <td class="py-4 px-5 text-center">
                                @php $bStatus = strtolower($booking->booking_status ?? 'confirmed'); @endphp
                                @if($bStatus === 'cancelled')
                                    <span class="inline-flex items-center px-2.5 py-1 bg-red-100 text-red-800 text-xs font-semibold rounded-lg">Đã hủy</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 bg-emerald-100 text-emerald-800 text-xs font-semibold rounded-lg">Đã xác nhận</span>
                                @endif
                            </td>
                            <td class="py-4 px-5 text-center">
                                @if(is_null($booking->printed_at))
                                    <span class="inline-flex items-center px-2.5 py-1 bg-amber-50 text-amber-700 text-xs font-semibold rounded-lg">Chưa in</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 bg-emerald-50 text-emerald-700 text-xs font-semibold rounded-lg">Đã in</span>
                                @endif
                            </td>
                            <td class="py-4 px-5 text-right">
                                @if(is_null($booking->printed_at))
                                    <a href="{{ route('staff.combo-bookings.print-receipt', $booking->id) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 text-xs font-bold rounded-lg border border-amber-200 transition-colors">🧾 In biên lai</a>
                                @else
                                    <span class="inline-flex items-center px-3 py-1.5 bg-emerald-50 text-emerald-700 text-xs font-bold rounded-lg border border-emerald-200">✅ Đã in</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-16 text-center">
                                <div class="max-w-sm mx-auto flex flex-col items-center">
                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center text-gray-400 mb-4">
                                        <span class="material-symbols-outlined text-3xl">fastfood</span>
                                    </div>
                                    <h3 class="text-base font-bold text-gray-900 mb-1">Không có đơn combo nào trong ngày này</h3>
                                    <p class="text-xs text-gray-500">Chưa có đơn combo/F&B nào được tạo cho rạp này trong ngày <span class="font-semibold text-gray-700">{{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}</span>.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($bookings->hasPages())
            <div class="p-4 border-t border-gray-200 bg-gray-50/50">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
