{{-- Component: Dropdown menu Báo cáo doanh thu --}}
<div class="relative ml-4 flex items-center">
    {{-- Nút mở dropdown --}}
    <button 
        type="button" 
        class="flex items-center gap-1.5 hover:text-primary transition-colors text-on-surface-variant font-medium text-sm"
        id="revenueDropdownButton"
    >
        <span class="material-symbols-outlined text-[20px]">bar_chart</span>
        Báo cáo doanh thu
        <span class="material-symbols-outlined text-[18px]">arrow_drop_down</span>
    </button>

    {{-- Khung menu thả xuống (Tạm ẩn bằng class hidden, sẽ được xử lý logic ở commit 3) --}}
    <div 
        id="revenueDropdownMenu"
        class="hidden absolute top-full left-0 mt-2 w-56 bg-surface-container-lowest rounded-xl shadow-ambient-sm border border-outline-variant z-50 overflow-hidden"
    >
        <ul class="py-1.5 flex flex-col">
            <li>
                <a href="{{ route('admin.reports.cinema') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-on-surface hover:bg-surface-container-low transition-colors {{ request()->routeIs('admin.reports.cinema') ? 'bg-surface-container-low font-semibold text-primary' : '' }}">
                    <span class="material-symbols-outlined text-[18px]">storefront</span>
                    Báo cáo theo Rạp
                </a>
            </li>
            <li>
                <a href="{{ route('admin.reports.movie') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-on-surface hover:bg-surface-container-low transition-colors {{ request()->routeIs('admin.reports.movie') ? 'bg-surface-container-low font-semibold text-primary' : '' }}">
                    <span class="material-symbols-outlined text-[18px]">movie</span>
                    Báo cáo theo Phim
                </a>
            </li>
        </ul>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btn = document.getElementById('revenueDropdownButton');
        const menu = document.getElementById('revenueDropdownMenu');

        if (btn && menu) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                menu.classList.toggle('hidden');
            });

            document.addEventListener('click', function(e) {
                if (!btn.contains(e.target) && !menu.contains(e.target)) {
                    menu.classList.add('hidden');
                }
            });
        }
    });
</script>
@endpush
