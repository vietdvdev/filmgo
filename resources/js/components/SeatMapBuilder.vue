<template>
  <div class="seat-map-builder font-sans text-sm text-slate-800">

    <!-- ═══════════════════════════════════════════════════════════════════
         TOOLBAR — Cấu hình lưới & loại ghế đang chọn để tô màu
    ═══════════════════════════════════════════════════════════════════ -->
    <div class="flex flex-wrap items-end gap-4 bg-white border border-slate-200 p-4 mb-4">

      <!-- Số hàng -->
      <div class="flex flex-col gap-1">
        <label class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Số hàng</label>
        <input
          v-model.number="rows"
          type="number" min="1" max="26"
          class="w-20 px-3 py-1.5 border border-slate-300 text-sm focus:outline-none focus:border-blue-500 rounded-none"
          @change="rebuildGrid"
        />
      </div>

      <!-- Số cột -->
      <div class="flex flex-col gap-1">
        <label class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Số cột</label>
        <input
          v-model.number="cols"
          type="number" min="1" max="99"
          class="w-20 px-3 py-1.5 border border-slate-300 text-sm focus:outline-none focus:border-blue-500 rounded-none"
          @change="rebuildGrid"
        />
      </div>

      <!-- Nút tái tạo lưới -->
      <button
        @click="rebuildGrid"
        class="px-4 py-1.5 bg-slate-700 hover:bg-slate-900 text-white text-xs font-bold uppercase tracking-wide transition-colors rounded-none"
      >
        Áp dụng lưới
      </button>

      <div class="w-px h-8 bg-slate-200 hidden sm:block"></div>

      <!-- Bộ chọn loại ghế để tô nhanh (Brush) -->
      <div class="flex flex-col gap-1">
        <label class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Loại tô khi click</label>
        <div class="flex gap-1.5 flex-wrap">
          <!-- Nút "Lối đi / Xóa" -->
          <button
            @click="activeBrush = null"
            :class="[
              'px-3 py-1.5 text-xs font-bold border transition-all rounded-none',
              activeBrush === null
                ? 'bg-slate-800 text-white border-slate-800'
                : 'bg-white text-slate-600 border-slate-300 hover:border-slate-500',
            ]"
          >
            ✕ Lối đi
          </button>

          <!-- Nút từng loại ghế từ props -->
          <button
            v-for="type in seatTypes"
            :key="type.id"
            @click="activeBrush = type"
            :class="[
              'px-3 py-1.5 text-xs font-bold border transition-all rounded-none',
              activeBrush?.id === type.id
                ? `${seatTypeStyle(type.id).activeCls}`
                : 'bg-white text-slate-600 border-slate-300 hover:border-slate-500',
            ]"
          >
            {{ type.name }}
          </button>
        </div>
      </div>

      <div class="flex-1"></div>

      <!-- Thống kê nhanh -->
      <div class="text-[11px] text-slate-500 text-right">
        <div>Tổng ghế hoạt động: <span class="font-bold text-slate-800">{{ activeSeatCount }}</span></div>
        <div>Bảo trì: <span class="font-bold text-amber-600">{{ maintenanceCount }}</span></div>
        <div>Lối đi: <span class="font-bold text-slate-800">{{ aisleCount }}</span></div>
      </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════
         LEGEND — Chú thích màu sắc
    ═══════════════════════════════════════════════════════════════════ -->
    <div class="flex flex-wrap gap-3 mb-4 text-[11px] font-semibold text-slate-600">
      <div class="flex items-center gap-1.5">
        <div class="w-4 h-4 border border-dashed border-slate-300 bg-white"></div>
        Lối đi / Trống
      </div>
      <div
        v-for="type in seatTypes"
        :key="'legend-' + type.id"
        class="flex items-center gap-1.5"
      >
        <div class="w-4 h-4 rounded-none" :class="seatTypeStyle(type.id).dot"></div>
        {{ type.name }}
        <span class="text-slate-400">(+{{ Number(type.surcharge_price ?? 0).toLocaleString('vi-VN') }}đ)</span>
      </div>
      <div class="flex items-center gap-1.5">
        <div class="w-4 h-4 bg-red-200 border border-red-400 rounded-none"></div>
        Bảo trì
      </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════
         MÀN HÌNH ẢO
    ═══════════════════════════════════════════════════════════════════ -->
    <div class="text-center mb-8">
      <div class="inline-block w-1/2 bg-slate-700 text-white text-[10px] font-black uppercase tracking-widest py-1.5">
        ▲ MÀN HÌNH CHIẾU PHIM ▲
      </div>
      <div class="w-1/2 mx-auto h-0.5 bg-gradient-to-r from-transparent via-slate-400 to-transparent mt-0.5"></div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════
         SEAT GRID — Ma trận ghế 2 chiều
    ═══════════════════════════════════════════════════════════════════ -->
    <div class="overflow-x-auto pb-4">
      <div class="inline-flex flex-col gap-1.5 min-w-max mx-auto">

        <!-- Tiêu đề số cột -->
        <div class="flex items-center gap-1.5">
          <div class="w-6"></div> <!-- căn hàng với label bên trái -->
          <div
            v-for="col in cols"
            :key="'col-header-' + col"
            class="w-9 h-5 flex items-center justify-center text-[10px] font-bold text-slate-400"
          >
            {{ col }}
          </div>
        </div>

        <!-- Từng hàng ghế -->
        <div
          v-for="(rowSeats, rowIdx) in grid"
          :key="'row-' + rowIdx"
          class="flex items-center gap-1.5"
        >
          <!-- Nhãn hàng bên trái (Clickable để tô nhanh cả hàng) -->
          <button
            type="button"
            @click="handleRowClick(rowIdx)"
            title="Nhấp để tô nhanh toàn bộ hàng ghế này"
            class="w-6 h-9 flex items-center justify-center text-xs font-black text-slate-500 hover:bg-slate-100 hover:text-blue-600 transition-colors cursor-pointer select-none rounded-none"
          >
            {{ rowLabel(rowIdx) }}
          </button>

          <!-- Các ô ghế trong hàng -->
          <div
            v-for="(seat, colIdx) in rowSeats"
            :key="`seat-${rowIdx}-${colIdx}`"
            @click="handleSeatClick(rowIdx, colIdx)"
            :title="seat.state === null
              ? `${rowLabel(rowIdx)}${colIdx + 1} — Lối đi`
              : `${rowLabel(rowIdx)}${colIdx + 1} — ${stateMeta(seat.state).label}`"
            :class="[
              'w-9 h-9 flex items-center justify-center',
              'text-[9px] font-bold leading-none',
              'border cursor-pointer select-none',
              'transition-all duration-100 hover:scale-110 hover:z-10 relative',
              seatCellClass(seat, rowIdx, colIdx),
            ]"
          >
            <!-- Lối đi: không hiển thị gì -->
            <template v-if="seat.state === null">
              <span class="text-slate-200 text-[10px]">·</span>
            </template>

            <!-- Ghế có trạng thái -->
            <template v-else>
              <span v-if="seat.state === STATES.MAINTENANCE" class="text-[10px]">🔧</span>
              <span v-else class="text-[9px]">{{ rowLabel(rowIdx) }}{{ colIdx + 1 }}</span>
            </template>
          </div>

          <!-- Nhãn hàng bên phải (Clickable để tô nhanh cả hàng) -->
          <button
            type="button"
            @click="handleRowClick(rowIdx)"
            title="Nhấp để tô nhanh toàn bộ hàng ghế này"
            class="w-6 h-9 flex items-center justify-center text-xs font-black text-slate-500 hover:bg-slate-100 hover:text-blue-600 transition-colors cursor-pointer select-none rounded-none"
          >
            {{ rowLabel(rowIdx) }}
          </button>
        </div>

      </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════
         ACTION BAR — Lưu & Reset
    ═══════════════════════════════════════════════════════════════════ -->
    <div class="flex items-center justify-between border-t border-slate-200 pt-4 mt-4 gap-4">
      <div class="flex gap-2">
        <button
          @click="loadInitialSeats"
          class="px-4 py-2 border border-slate-300 text-slate-700 text-xs font-bold uppercase hover:bg-slate-50 transition-colors rounded-none"
        >
          ↺ Khôi phục sơ đồ cũ
        </button>
        <button
          @click="resetGrid"
          class="px-4 py-2 border border-red-200 text-red-700 text-xs font-bold uppercase hover:bg-red-50 hover:border-red-300 transition-colors rounded-none"
        >
          ✕ Xóa sạch lưới
        </button>
      </div>

      <div class="flex items-center gap-3">
        <!-- Thông báo trạng thái -->
        <transition name="fade">
          <span
            v-if="statusMsg.text"
            :class="[
              'text-xs font-semibold px-3 py-1 border rounded-none',
              statusMsg.type === 'success' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-red-50 text-red-700 border-red-200',
            ]"
          >
            {{ statusMsg.text }}
          </span>
        </transition>

        <!-- Lưu: cho phép lưu khi có ít nhất 1 ghế (kể cả bảo trì) -->
        <button
          @click="saveMap"
          :disabled="isSaving || totalSeatCount === 0"
          :class="[
            'px-6 py-2 text-xs font-black uppercase tracking-wide transition-all rounded-none',
            isSaving || totalSeatCount === 0
              ? 'bg-slate-200 text-slate-400 cursor-not-allowed'
              : 'bg-blue-600 hover:bg-blue-700 text-white',
          ]"
        >
          <span v-if="isSaving">⏳ Đang lưu...</span>
          <span v-else>💾 Lưu sơ đồ ({{ totalSeatCount }} ghế)</span>
        </button>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

// ─── Props ────────────────────────────────────────────────────────────────────
const props = defineProps({
  /**
   * ID của phòng chiếu — dùng để gọi API lưu sơ đồ.
   * @type {Number|String}
   */
  roomId: {
    type: [Number, String],
    required: true,
  },

  /**
   * URL đầy đủ để gọi API đồng bộ ghế.
   * Được truyền từ Blade qua data-sync-url để đảm bảo đúng base URL
   * trên mọi cấu hình (virtual host, thư mục con, v.v.).
   * @type {String}
   */
  syncUrl: {
    type: String,
    required: true,
  },

  /**
   * Danh sách loại ghế từ DB (seat_types):
   * [{ id, name, surcharge_price }, ...]
   * @type {Array}
   */
  seatTypes: {
    type: Array,
    required: true,
    validator: (val) => val.every(t => 'id' in t && 'name' in t),
  },

  /**
   * Danh sách ghế hiện có từ DB:
   * [{ seat_row, seat_number, seat_type_id, status }, ...]
   * @type {Array}
   */
  initialSeats: {
    type: Array,
    default: () => [],
  },
})

// ─── Hằng số trạng thái ghế ───────────────────────────────────────────────────
const STATES = {
  STANDARD:    'standard',
  VIP:         'vip',
  SWEETBOX:    'sweetbox',
  MAINTENANCE: 'maintenance',
}

// ─── State (Reactive) ─────────────────────────────────────────────────────────
const rows        = ref(10)
const cols        = ref(15)
const grid        = ref([])       // grid[rowIdx][colIdx] = { state: string|null }
const activeBrush = ref(null)     // null = lối đi, hoặc SeatType object
const isSaving    = ref(false)
const statusMsg   = ref({ text: '', type: 'success' })
let statusTimer   = null

// ─── Helpers: tên hàng (0→A, 1→B ...) ───────────────────────────────────────
function rowLabel(idx) {
  return String.fromCharCode(65 + idx)
}

// ─── Khởi tạo / Tái tạo lưới 2 chiều ─────────────────────────────────────────
function buildGrid(numRows, numCols) {
  return Array.from({ length: numRows }, () =>
    Array.from({ length: numCols }, () => ({ state: null }))
  )
}

function rebuildGrid() {
  const r = Math.max(1, Math.min(26, rows.value))
  const c = Math.max(1, Math.min(99, cols.value))
  rows.value = r
  cols.value = c
  grid.value = buildGrid(r, c)
}

function resetGrid() {
  grid.value = buildGrid(rows.value, cols.value)
}

// ─── Ánh xạ seat_type_id → state (hỗ trợ không giới hạn số loại ghế) ─────────
/**
 * FIX: Không hard-code index 0/1/2 mà dùng seat_type_id trực tiếp làm key.
 * Điều này giúp hỗ trợ bất kỳ số lượng loại ghế nào trong DB.
 */
function seatTypeIdToState(seatTypeId) {
  const numId = Number(seatTypeId)
  const idx = props.seatTypes.findIndex(t => Number(t.id) === numId)
  if (idx < 0) return STATES.STANDARD // fallback an toàn
  // Ánh xạ theo index trong mảng seatTypes (đúng với mọi số lượng loại ghế)
  const stateMap = [STATES.STANDARD, STATES.VIP, STATES.SWEETBOX]
  return stateMap[idx] ?? `custom_${numId}` // loại thứ 4+ trở đi dùng custom key
}

function stateToSeatTypeId(state) {
  // state có thể là 'standard','vip','sweetbox' hoặc 'custom_N'
  if (!state) return props.seatTypes[0]?.id
  if (state.startsWith('custom_')) {
    return parseInt(state.replace('custom_', ''), 10)
  }
  const stateOrder = [STATES.STANDARD, STATES.VIP, STATES.SWEETBOX]
  const idx = stateOrder.indexOf(state)
  return props.seatTypes[idx]?.id ?? props.seatTypes[0]?.id
}

function seatTypeToState(type) {
  return seatTypeIdToState(type.id)
}

function isSweetboxState(state) {
  if (!state) return false
  if (state === STATES.SWEETBOX) return true
  const seatTypeId = stateToSeatTypeId(state)
  const type = props.seatTypes.find(t => Number(t.id) === Number(seatTypeId))
  if (!type) return false
  const name = (type.name || '').toLowerCase()
  return name.includes('sweetbox') || name.includes('couple') || name.includes('đôi') || name.includes('doi')
}

function isSweetboxBrush() {
  if (!activeBrush.value) return false
  const name = (activeBrush.value.name || '').toLowerCase()
  return name.includes('sweetbox') || name.includes('couple') || name.includes('đôi') || name.includes('doi')
}

// ─── Tải sơ đồ ghế ban đầu từ DB ─────────────────────────────────────────────
function loadInitialSeats() {
  if (!props.initialSeats || props.initialSeats.length === 0) {
    rebuildGrid()
    return
  }

  let maxRowIdx = 9   // Tối thiểu 10 hàng
  let maxColIdx = 14  // Tối thiểu 15 cột

  props.initialSeats.forEach(seat => {
    const rowChar = seat.seat_row.toUpperCase()
    const rowIdx  = rowChar.charCodeAt(0) - 65
    const colIdx  = seat.seat_number - 1

    if (rowIdx > maxRowIdx && rowIdx < 26) maxRowIdx = rowIdx
    if (colIdx > maxColIdx && colIdx < 99) maxColIdx = colIdx
  })

  rows.value = maxRowIdx + 1
  cols.value = maxColIdx + 1
  grid.value = buildGrid(rows.value, cols.value)

  props.initialSeats.forEach(seat => {
    const rowChar = seat.seat_row.toUpperCase()
    const rowIdx  = rowChar.charCodeAt(0) - 65
    const colIdx  = seat.seat_number - 1

    if (rowIdx >= 0 && rowIdx < rows.value && colIdx >= 0 && colIdx < cols.value) {
      grid.value[rowIdx][colIdx].state = seat.status === 'maintenance'
        ? STATES.MAINTENANCE
        : seatTypeIdToState(seat.seat_type_id)
    }
  })
}

onMounted(() => {
  loadInitialSeats()

  // Đặt CSRF token mặc định cho tất cả request Axios
  const metaToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
  if (metaToken) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = metaToken
  }
})

// ─── Logic click ghế (Brush mode) ────────────────────────────────────────────
function handleSeatClick(rowIdx, colIdx) {
  const seat = grid.value[rowIdx][colIdx]
  const siblingColIdx = (colIdx % 2 === 0) ? colIdx + 1 : colIdx - 1
  const hasSibling = siblingColIdx >= 0 && siblingColIdx < cols.value
  const siblingSeat = hasSibling ? grid.value[rowIdx][siblingColIdx] : null

  const brushIsSweetbox = isSweetboxBrush()
  const currentIsSweetbox = isSweetboxState(seat.state)
  const siblingIsSweetbox = siblingSeat ? isSweetboxState(siblingSeat.state) : false

  // Trường hợp 1: Chọn cọ Sweetbox
  if (brushIsSweetbox) {
    if (!hasSibling) {
      showStatus('⚠ Ghế Sweetbox bắt buộc phải tạo theo cặp 2 ghế dính liền.', 'error')
      return
    }

    const brushState = seatTypeToState(activeBrush.value)

    if (seat.state === brushState && siblingSeat.state === brushState) {
      // Đã là cặp Sweetbox -> click chuyển vòng cả cặp sang bảo trì
      seat.state = STATES.MAINTENANCE
      siblingSeat.state = STATES.MAINTENANCE
    } else if (seat.state === STATES.MAINTENANCE && siblingSeat.state === STATES.MAINTENANCE) {
      // Đang là bảo trì -> chuyển cả cặp về Lối đi
      seat.state = null
      siblingSeat.state = null
    } else {
      // Chưa đủ cặp Sweetbox -> tô cả 2 thành cặp Sweetbox dính liền
      seat.state = brushState
      siblingSeat.state = brushState
    }
    return
  }

  // Trường hợp 2: Ghế hiện tại hoặc ghế đối tác là Sweetbox (Xóa/Thay đổi loại ghế)
  if (currentIsSweetbox || siblingIsSweetbox) {
    if (hasSibling) {
      if (activeBrush.value === null) {
        // Cọ Lối đi (Null) -> XÓA CẢ CẶP SWEETBOX
        seat.state = null
        siblingSeat.state = null
      } else {
        // Đổi loại ghế / bảo trì khác -> áp dụng đồng thời cho cả cặp
        const brushState = seatTypeToState(activeBrush.value)
        if (seat.state === brushState && siblingSeat.state === brushState) {
          seat.state = STATES.MAINTENANCE
          siblingSeat.state = STATES.MAINTENANCE
        } else if (seat.state === STATES.MAINTENANCE && siblingSeat.state === STATES.MAINTENANCE) {
          seat.state = null
          siblingSeat.state = null
        } else {
          seat.state = brushState
          siblingSeat.state = brushState
        }
      }
      return
    }
  }

  // Trường hợp 3: Ghế đơn thông thường (Standard, VIP...)
  if (activeBrush.value === null) {
    seat.state = null
    return
  }

  const brushState = seatTypeToState(activeBrush.value)

  if (seat.state === null) {
    seat.state = brushState
  } else if (seat.state === brushState) {
    seat.state = STATES.MAINTENANCE
  } else if (seat.state === STATES.MAINTENANCE) {
    seat.state = null
  } else {
    seat.state = brushState
  }
}

// ─── Logic click nhãn hàng (Tô/Tạo nhanh cả hàng ghế cùng lúc) ───────────────
function handleRowClick(rowIdx) {
  const rowSeats = grid.value[rowIdx]

  if (activeBrush.value === null) {
    // Chọn cọ "Lối đi" (null) -> Xóa toàn bộ hàng
    rowSeats.forEach(seat => {
      seat.state = null
    })
    return
  }

  const brushIsSweetbox = isSweetboxBrush()
  const brushState = seatTypeToState(activeBrush.value)

  if (brushIsSweetbox) {
    // Tô Sweetbox theo từng cặp 2 ghế liên tiếp (0-1, 2-3, 4-5...)
    for (let c = 0; c < cols.value; c += 2) {
      if (c + 1 < cols.value) {
        rowSeats[c].state = brushState
        rowSeats[c + 1].state = brushState
      } else {
        // Cột lẻ cuối cùng không ghép được cặp -> giữ nguyên hoặc chuyển về lối đi
        if (rowSeats[c].state === brushState) {
          rowSeats[c].state = null
        }
      }
    }
    return
  }

  // Loại ghế thông thường
  const allMatched = rowSeats.every(seat => seat.state === brushState)

  if (allMatched) {
    rowSeats.forEach(seat => {
      seat.state = STATES.MAINTENANCE
    })
  } else {
    rowSeats.forEach(seat => {
      seat.state = brushState
    })
  }
}

// ─── CSS class cho từng ô ghế ────────────────────────────────────────────────
function seatCellClass(seat, rowIdx, colIdx) {
  if (seat.state === null) {
    return 'bg-white border-dashed border-slate-200 hover:bg-slate-50 rounded-none'
  }

  let extraClass = ''
  if (isSweetboxState(seat.state) && typeof rowIdx === 'number' && typeof colIdx === 'number') {
    const siblingColIdx = (colIdx % 2 === 0) ? colIdx + 1 : colIdx - 1
    if (siblingColIdx >= 0 && siblingColIdx < cols.value) {
      const siblingSeat = grid.value[rowIdx]?.[siblingColIdx]
      if (siblingSeat && isSweetboxState(siblingSeat.state)) {
        if (colIdx % 2 === 0) {
          extraClass = ' border-r-0 rounded-r-none z-10'
        } else {
          extraClass = ' border-l-0 rounded-l-none z-10'
        }
      }
    }
  }

  if (seat.state === STATES.MAINTENANCE) {
    return 'bg-red-100 border-red-400 text-red-700 hover:bg-red-200 rounded-none' + extraClass
  }
  // Lấy seat_type_id từ state, rồi lấy style
  const seatTypeId = stateToSeatTypeId(seat.state)
  return seatTypeStyle(seatTypeId).cell + ' rounded-none' + extraClass
}

/**
 * Trả về Tailwind classes cho từng loại ghế.
 * FIX: Hỗ trợ tối đa 6 preset màu; loại thừa dùng màu neutral.
 */
function seatTypeStyle(seatTypeId) {
  const idx = props.seatTypes.findIndex(t => Number(t.id) === Number(seatTypeId))
  const styles = [
    // index 0 — Thường / Standard
    {
      cell:      'bg-slate-200 border-slate-400 text-slate-800 hover:bg-slate-300',
      dot:       'bg-slate-200 border border-slate-400',
      activeCls: 'bg-slate-700 text-white border-slate-700',
    },
    // index 1 — VIP
    {
      cell:      'bg-purple-500 border-purple-700 text-white hover:bg-purple-600',
      dot:       'bg-purple-500 border border-purple-700',
      activeCls: 'bg-purple-700 text-white border-purple-700',
    },
    // index 2 — Sweetbox
    {
      cell:      'bg-pink-400 border-pink-600 text-white hover:bg-pink-500',
      dot:       'bg-pink-400 border border-pink-600',
      activeCls: 'bg-pink-600 text-white border-pink-700',
    },
    // index 3 — Loại thứ 4 (VD: Ghế đôi)
    {
      cell:      'bg-amber-400 border-amber-600 text-white hover:bg-amber-500',
      dot:       'bg-amber-400 border border-amber-600',
      activeCls: 'bg-amber-600 text-white border-amber-700',
    },
    // index 4 — Loại thứ 5
    {
      cell:      'bg-teal-500 border-teal-700 text-white hover:bg-teal-600',
      dot:       'bg-teal-500 border border-teal-700',
      activeCls: 'bg-teal-700 text-white border-teal-700',
    },
    // index 5 — Loại thứ 6
    {
      cell:      'bg-orange-400 border-orange-600 text-white hover:bg-orange-500',
      dot:       'bg-orange-400 border border-orange-600',
      activeCls: 'bg-orange-600 text-white border-orange-700',
    },
  ]
  // Nếu idx < 0 hoặc ngoài danh sách → dùng màu neutral cuối cùng
  return styles[idx] ?? styles[0]
}

function stateMeta(state) {
  if (state === STATES.MAINTENANCE) return { label: 'Bảo trì' }
  const seatTypeId = stateToSeatTypeId(state)
  const type = props.seatTypes.find(t => Number(t.id) === Number(seatTypeId))
  return { label: type?.name ?? 'Ghế' }
}

// ─── Computed: đếm ghế ────────────────────────────────────────────────────────
// Ghế đang hoạt động (không tính bảo trì, không tính lối đi)
const activeSeatCount = computed(() =>
  grid.value.flat().filter(s => s.state !== null && s.state !== STATES.MAINTENANCE).length
)

// Ghế bảo trì
const maintenanceCount = computed(() =>
  grid.value.flat().filter(s => s.state === STATES.MAINTENANCE).length
)

// Lối đi / trống
const aisleCount = computed(() =>
  grid.value.flat().filter(s => s.state === null).length
)

// FIX: totalSeatCount = active + maintenance, dùng để enable/disable nút Lưu
// Cho phép lưu khi có ít nhất 1 ô có ghế (kể cả bảo trì)
const totalSeatCount = computed(() =>
  grid.value.flat().filter(s => s.state !== null).length
)

// ─── Lưu sơ đồ ghế lên backend ───────────────────────────────────────────────
/**
 * POST tới Web route: /manager/rooms/{roomId}/sync-seats
 * Gom tất cả ghế (state !== null) thành mảng, bao gồm ghế bảo trì.
 * Lối đi (state === null) bị bỏ qua.
 */
async function saveMap() {
  if (isSaving.value || totalSeatCount.value === 0) return

  const seats = []

  grid.value.forEach((rowSeats, rowIdx) => {
    rowSeats.forEach((seat, colIdx) => {
      if (seat.state === null) return // bỏ qua lối đi

      seats.push({
        seat_row:     rowLabel(rowIdx),
        seat_number:  colIdx + 1,
        seat_type_id: stateToSeatTypeId(seat.state),
        status:       seat.state === STATES.MAINTENANCE ? 'maintenance' : 'active',
      })
    })
  })

  isSaving.value = true
  clearStatus()

  try {
    // Gọi Web route dùng syncUrl từ Blade (đảm bảo đúng base URL)
    const response = await axios.post(
      props.syncUrl,
      { seats },
      {
        headers: {
          'Content-Type': 'application/json',
          'Accept':        'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      }
    )

    if (response.data?.success) {
      showStatus(`✓ ${response.data.message}`, 'success')
    } else {
      showStatus(response.data?.message ?? 'Lưu thất bại.', 'error')
    }
  } catch (error) {
    if (error.response?.status === 422) {
      const errors   = error.response.data?.errors ?? {}
      const firstMsg = Object.values(errors).flat()[0] ?? 'Dữ liệu không hợp lệ.'
      showStatus(`✗ ${firstMsg}`, 'error')
    } else if (error.response?.status === 403) {
      showStatus('✗ Bạn không có quyền thực hiện thao tác này.', 'error')
    } else if (error.response?.status === 401) {
      showStatus('✗ Phiên đăng nhập hết hạn. Vui lòng đăng nhập lại.', 'error')
    } else {
      showStatus(`✗ Lỗi: ${error.response?.data?.message ?? error.message}`, 'error')
    }
    console.error('[SeatMapBuilder] saveMap error:', error.response?.data ?? error)
  } finally {
    isSaving.value = false
  }
}

// ─── Helper: hiển thị và tự ẩn thông báo ────────────────────────────────────
function showStatus(text, type = 'success') {
  statusMsg.value = { text, type }
  clearTimeout(statusTimer)
  statusTimer = setTimeout(() => {
    statusMsg.value = { text: '', type: 'success' }
  }, 6000)
}

function clearStatus() {
  clearTimeout(statusTimer)
  statusMsg.value = { text: '', type: 'success' }
}
</script>

<style scoped>
/* Transition cho toast thông báo */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
