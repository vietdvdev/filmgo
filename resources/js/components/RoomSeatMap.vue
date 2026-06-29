<template>
  <div class="room-seat-map bg-white border border-slate-200 shadow-md p-6 rounded-none">
    
    <!-- 1. Header & Title -->
    <div class="border-b border-slate-100 pb-4 mb-6">
      <h3 class="text-lg font-bold uppercase tracking-tight text-slate-900 flex items-center gap-2">
        <span class="material-symbols-outlined text-blue-600">grid_on</span>
        Thiết Lập Sơ Đồ Ghế Phòng Chiếu
      </h3>
      <p class="text-xs text-slate-500 mt-1">
        Nhấp chọn từng ghế để thiết lập trạng thái hoặc nhấp vào ký hiệu hàng (A, B, C...) ở cột trái để bật/tắt nhanh cả hàng.
      </p>
    </div>

    <!-- 2. Legend / Chú thích loại ghế -->
    <div class="flex flex-wrap gap-4 mb-8 text-xs font-semibold text-slate-600">
      <div class="flex items-center gap-2">
        <div class="w-5 h-5 bg-gray-100 border border-gray-300 rounded-none"></div>
        <span>Ghế Thường (Standard)</span>
      </div>
      <div class="flex items-center gap-2">
        <div class="w-5 h-5 bg-yellow-100 border border-yellow-300 rounded-none"></div>
        <span>Ghế VIP</span>
      </div>
      <div class="flex items-center gap-2">
        <div class="w-5 h-5 bg-rose-100 border border-rose-300 rounded-none"></div>
        <span>Ghế Đôi (Couple)</span>
      </div>
      <div class="flex items-center gap-2">
        <div class="w-5 h-5 bg-blue-600 border border-blue-700 rounded-none"></div>
        <span>Đang Chọn (Selected)</span>
      </div>
    </div>

    <!-- 3. Screen (Màn hình ảo) -->
    <div class="w-full flex flex-col items-center">
      <div class="w-full max-w-2xl text-center">
        <div class="h-10 border-t-8 border-gray-400 rounded-t-[50%] shadow-sm flex items-end justify-center mb-12">
          <span class="text-[10px] font-black text-gray-500 tracking-widest uppercase pb-1">Màn Hình Chiếu Phim</span>
        </div>
      </div>
    </div>

    <!-- 4. Seat Matrix (Ma trận ghế) -->
    <div class="w-full overflow-x-auto pb-4">
      <div class="min-w-[600px] flex flex-col items-center">
        <div 
          v-for="(rowSeats, rowName) in groupedSeats" 
          :key="rowName" 
          class="flex flex-row items-center justify-center gap-2 mb-2 w-full"
        >
          <!-- Cột nhãn hàng ở bên trái (Clickable để toggle toàn bộ hàng) -->
          <button 
            type="button"
            @click="toggleRow(rowName)"
            class="w-8 h-8 flex items-center justify-center cursor-pointer font-bold rounded-none hover:bg-blue-100 hover:text-blue-600 border border-transparent hover:border-blue-300 transition-colors uppercase text-xs text-slate-500"
            title="Nhấp để chọn/bỏ chọn toàn bộ hàng"
          >
            {{ rowName }}
          </button>

          <!-- Danh sách các ghế trong hàng -->
          <div class="flex flex-row gap-2">
            <button
              v-for="seat in rowSeats"
              :key="seat.id"
              type="button"
              @click="toggleSeat(seat)"
              :class="[
                'w-10 h-10 flex items-center justify-center text-xs cursor-pointer border rounded-none transition-all duration-100 hover:scale-105 select-none font-bold',
                seat.is_selected 
                  ? 'bg-blue-600 border-blue-700 text-white font-bold' 
                  : seat.type === 'standard' 
                    ? 'bg-gray-100 border-gray-300 text-gray-700 hover:bg-gray-200' 
                    : seat.type === 'vip' 
                      ? 'bg-yellow-100 border-yellow-300 text-yellow-700 hover:bg-yellow-200' 
                      : 'bg-rose-100 border-rose-300 text-rose-700 hover:bg-rose-200'
              ]"
              :title="`Ghế ${seat.name} - ${seat.type}`"
            >
              {{ seat.name }}
            </button>
          </div>

          <!-- Nhãn hàng bên phải -->
          <span class="w-8 h-8 flex items-center justify-center font-bold text-xs text-slate-400 select-none uppercase">
            {{ rowName }}
          </span>
        </div>
      </div>
    </div>

    <!-- 5. Actions & Submission Footer -->
    <div class="border-t border-slate-200 pt-6 mt-6 flex flex-col sm:flex-row justify-between items-center gap-4">
      <div class="text-xs text-slate-500">
        Đã chọn: <span class="font-bold text-blue-600 text-sm">{{ selectedSeatIds.length }}</span> ghế
      </div>
      
      <div class="flex gap-3">
        <button
          type="button"
          @click="resetSelection"
          class="px-5 py-2.5 border border-slate-300 text-slate-700 text-xs font-bold uppercase tracking-wide hover:bg-slate-50 transition-colors rounded-none"
        >
          Hủy Chọn Tất Cả
        </button>
        <button
          type="button"
          @click="submitLayout"
          :disabled="selectedSeatIds.length === 0"
          class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 disabled:bg-slate-200 disabled:text-slate-400 disabled:cursor-not-allowed text-white text-xs font-bold uppercase tracking-wide transition-colors rounded-none flex items-center gap-2"
        >
          <span class="material-symbols-outlined text-base">save</span>
          Xác Nhận Sơ Đồ
        </button>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

// ─── 1. MOCK DATA ─────────────────────────────────────────────────────────────
// Danh sách ghế mô phỏng API response (tối thiểu 3 hàng A, B, C có Standard, VIP và Couple)
const seats = ref([
  // Hàng A - Ghế Thường (Standard)
  { id: 1, name: 'A1', row: 'A', type: 'standard', is_selected: false },
  { id: 2, name: 'A2', row: 'A', type: 'standard', is_selected: false },
  { id: 3, name: 'A3', row: 'A', type: 'standard', is_selected: false },
  { id: 4, name: 'A4', row: 'A', type: 'standard', is_selected: false },
  { id: 5, name: 'A5', row: 'A', type: 'standard', is_selected: false },
  { id: 6, name: 'A6', row: 'A', type: 'standard', is_selected: false },

  // Hàng B - Ghế VIP
  { id: 7, name: 'B1', row: 'B', type: 'vip', is_selected: false },
  { id: 8, name: 'B2', row: 'B', type: 'vip', is_selected: false },
  { id: 9, name: 'B3', row: 'B', type: 'vip', is_selected: false },
  { id: 10, name: 'B4', row: 'B', type: 'vip', is_selected: false },
  { id: 11, name: 'B5', row: 'B', type: 'vip', is_selected: false },
  { id: 12, name: 'B6', row: 'B', type: 'vip', is_selected: false },

  // Hàng C - Ghế Đôi (Couple)
  { id: 13, name: 'C1', row: 'C', type: 'couple', is_selected: false },
  { id: 14, name: 'C2', row: 'C', type: 'couple', is_selected: false },
  { id: 15, name: 'C3', row: 'C', type: 'couple', is_selected: false },
  { id: 16, name: 'C4', row: 'C', type: 'couple', is_selected: false },
  { id: 17, name: 'C5', row: 'C', type: 'couple', is_selected: false },
  { id: 18, name: 'C6', row: 'C', type: 'couple', is_selected: false }
])

// ─── 2. COMPUTED PROPERTIES ──────────────────────────────────────────────────
// Chuyển mảng phẳng thành object gom nhóm theo hàng sử dụng Array.prototype.reduce()
const groupedSeats = computed(() => {
  return seats.value.reduce((acc, seat) => {
    const rowName = seat.row
    if (!acc[rowName]) {
      acc[rowName] = []
    }
    acc[rowName].push(seat)
    return acc
  }, {})
})

// Lấy danh sách ID của tất cả ghế đang được chọn
const selectedSeatIds = computed(() => {
  return getSelectedSeats()
})

// ─── 3. ACTIONS & CORE LOGIC ──────────────────────────────────────────────────
// Bật/tắt trạng thái chọn của một ghế
const toggleSeat = (seat) => {
  seat.is_selected = !seat.is_selected
}

// Bật/tắt trạng thái chọn cho cả hàng (Smart Toggle theo tên hàng)
const toggleRow = (rowName) => {
  // Lọc các ghế thuộc hàng này
  const rowSeats = seats.value.filter(seat => seat.row === rowName)
  
  if (rowSeats.length === 0) return

  // Kiểm tra xem TOÀN BỘ ghế trong hàng đã được chọn hay chưa
  const allSelected = rowSeats.every(seat => seat.is_selected === true)

  if (allSelected) {
    // Nếu tất cả đã được chọn -> Bỏ chọn tất cả ghế trong hàng
    rowSeats.forEach(seat => {
      seat.is_selected = false
    })
  } else {
    // Nếu có ít nhất một ghế chưa được chọn -> Chọn toàn bộ ghế trong hàng
    rowSeats.forEach(seat => {
      seat.is_selected = true
    })
  }
}

// Trả về mảng các IDs của tất cả các ghế được chọn
const getSelectedSeats = () => {
  return seats.value
    .filter(seat => seat.is_selected === true)
    .map(seat => seat.id)
}

// Bỏ chọn tất cả các ghế
const resetSelection = () => {
  seats.value.forEach(seat => {
    seat.is_selected = false
  })
}

// Submit xử lý (giả lập)
const submitLayout = () => {
  const selectedIds = getSelectedSeats()
  alert(`Đã xác nhận sơ đồ ghế! Các IDs được chọn: ${JSON.stringify(selectedIds)}`)
}
</script>
