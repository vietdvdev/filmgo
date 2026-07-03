<template>
  <div class="space-y-6 font-sans">
    <!-- Toast Notifications -->
    <div class="fixed top-4 right-4 z-50 space-y-2 pointer-events-none">
      <transition-group name="toast">
        <div v-for="toast in toasts" :key="toast.id"
             class="flex items-center gap-3 p-4 rounded-xl shadow-xl border text-sm max-w-md pointer-events-auto transition-all duration-300 backdrop-blur-md"
             :class="{
               'bg-emerald-50/90 border-emerald-200 text-emerald-800': toast.type === 'success',
               'bg-rose-50/90 border-rose-200 text-rose-800': toast.type === 'error',
               'bg-blue-50/90 border-blue-200 text-blue-800': toast.type === 'info'
             }">
          <span class="material-symbols-outlined shrink-0 text-xl"
                :class="{
                  'text-emerald-500': toast.type === 'success',
                  'text-rose-500': toast.type === 'error',
                  'text-blue-500': toast.type === 'info'
                }">
            {{ toast.type === 'success' ? 'check_circle' : toast.type === 'error' ? 'error' : 'info' }}
          </span>
          <span class="font-semibold flex-1 text-slate-800">{{ toast.message }}</span>
          <button @click="removeToast(toast.id)" class="ml-2 text-slate-400 hover:text-slate-600 transition-colors shrink-0">
            <span class="material-symbols-outlined text-base">close</span>
          </button>
        </div>
      </transition-group>
    </div>

    <!-- Header & Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white border border-slate-100 p-6 rounded-2xl shadow-sm relative overflow-hidden">
      <!-- Gradient Line Decor -->
      <div class="absolute top-0 left-0 right-0 h-[4px] bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-600"></div>

      <div>
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2.5">
          <span class="material-symbols-outlined text-blue-600 text-3xl">calendar_month</span>
          Lịch Chiếu & Suất Chiếu
        </h1>
        <p class="text-sm text-slate-500 mt-1">Dành cho Quản lý (Manager) - Điều phối lịch phát sóng, mở bán vé và giám sát trạng thái phòng chiếu.</p>
      </div>

      <div class="flex items-center flex-wrap gap-3">
        <!-- Nút mở bán hàng loạt suất chiếu đã chọn (Chỉ hiển thị khi có suất upcoming được chọn) -->
        <transition name="fade">
          <button
            v-if="selectedUpcomingIds.length > 0"
            @click="openBulkSales"
            class="flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white text-sm font-bold uppercase tracking-wider rounded-xl shadow-md shadow-emerald-500/10 transition-all duration-200"
          >
            <span class="material-symbols-outlined text-lg">lock_open</span>
            Mở bán đã chọn ({{ selectedUpcomingIds.length }})
          </button>
        </transition>

        <a
          :href="createUrl"
          class="flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white text-sm font-bold uppercase tracking-wider rounded-xl shadow-md shadow-blue-500/10 transition-all duration-200"
        >
          <span class="material-symbols-outlined text-lg">add</span>
          Tạo suất chiếu thủ công
        </a>

        <a
          :href="autoGenerateUrl"
          class="flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 active:scale-95 text-white text-sm font-bold uppercase tracking-wider rounded-xl shadow-md shadow-indigo-500/10 transition-all duration-200"
        >
          <span class="material-symbols-outlined text-lg">bolt</span>
          Xếp lịch tự động
        </a>
      </div>
    </div>

    <!-- Filter Bar (Grid ngang trên Desktop) -->
    <div class="bg-white border border-slate-100 p-6 rounded-2xl shadow-sm">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
        <!-- Rạp chiếu (Cinema_id - Bắt buộc chọn) -->
        <div class="space-y-1.5">
          <label for="filter_cinema_id" class="block text-xs font-bold uppercase tracking-wider text-slate-500">Rạp Chiếu <span class="text-rose-500">*</span></label>
          <div class="relative">
            <select
              id="filter_cinema_id"
              v-model="filters.cinema_id"
              class="block w-full pl-3 pr-10 py-2.5 border border-slate-200 text-sm rounded-xl focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/10 bg-slate-50/50 appearance-none font-medium text-slate-800 transition-all"
            >
              <option v-for="cinema in cinemas" :key="cinema.id" :value="cinema.id">
                {{ cinema.name }}
              </option>
            </select>
            <span class="material-symbols-outlined absolute right-3 top-2.5 text-slate-400 pointer-events-none text-xl">keyboard_arrow_down</span>
          </div>
        </div>

        <!-- Phòng chiếu (Room_id - Cascading theo Rạp chiếu) -->
        <div class="space-y-1.5">
          <label for="filter_room_id" class="block text-xs font-bold uppercase tracking-wider text-slate-500">Phòng Chiếu</label>
          <div class="relative">
            <select
              id="filter_room_id"
              v-model="filters.room_id"
              :disabled="!filters.cinema_id || loadingRooms"
              class="block w-full pl-3 pr-10 py-2.5 border border-slate-200 text-sm rounded-xl focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/10 bg-slate-50/50 appearance-none font-medium text-slate-800 transition-all disabled:bg-slate-100 disabled:text-slate-400"
            >
              <option :value="null">{{ loadingRooms ? 'Đang tải phòng...' : '-- Tất cả phòng --' }}</option>
              <option v-for="room in rooms" :key="room.id" :value="room.id">
                {{ room.name }}
              </option>
            </select>
            <span class="material-symbols-outlined absolute right-3 top-2.5 text-slate-400 pointer-events-none text-xl">keyboard_arrow_down</span>
          </div>
        </div>

        <!-- Phim (Movie_id - Tuỳ chọn) -->
        <div class="space-y-1.5">
          <label for="filter_movie_id" class="block text-xs font-bold uppercase tracking-wider text-slate-500">Phim</label>
          <div class="relative">
            <select
              id="filter_movie_id"
              v-model="filters.movie_id"
              class="block w-full pl-3 pr-10 py-2.5 border border-slate-200 text-sm rounded-xl focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/10 bg-slate-50/50 appearance-none font-medium text-slate-800 transition-all"
            >
              <option :value="null">-- Tất cả phim --</option>
              <option v-for="movie in movies" :key="movie.id" :value="movie.id">
                {{ movie.title }}
              </option>
            </select>
            <span class="material-symbols-outlined absolute right-3 top-2.5 text-slate-400 pointer-events-none text-xl">keyboard_arrow_down</span>
          </div>
        </div>

        <!-- Ngày chiếu (Date - Mặc định hôm nay) -->
        <div class="space-y-1.5">
          <label for="filter_date" class="block text-xs font-bold uppercase tracking-wider text-slate-500">Ngày Chiếu</label>
          <input
            id="filter_date"
            type="date"
            v-model="filters.date"
            class="block w-full px-3 py-2 border border-slate-200 text-sm rounded-xl focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/10 bg-slate-50/50 font-medium text-slate-800 transition-all"
          />
        </div>

        <!-- Nút Reset -->
        <div class="flex gap-2">
          <button
            @click="resetFilters"
            class="w-full py-2.5 px-4 border border-slate-200 hover:bg-slate-50 active:scale-98 text-slate-700 text-sm font-bold uppercase tracking-wider rounded-xl transition-all flex items-center justify-center gap-2"
          >
            <span class="material-symbols-outlined text-lg">restart_alt</span>
            Làm mới
          </button>
        </div>
      </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white border border-slate-100 rounded-2xl shadow-sm relative overflow-hidden">
      <!-- Loading Overlay -->
      <transition name="fade">
        <div v-if="loading" class="absolute inset-0 bg-white/80 z-20 flex items-center justify-center backdrop-blur-sm">
          <div class="flex flex-col items-center gap-3">
            <svg class="animate-spin h-10 w-10 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-xs text-slate-500 font-bold uppercase tracking-widest">Đang cập nhật lịch chiếu...</span>
          </div>
        </div>
      </transition>

      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50/75 border-b border-slate-100">
              <!-- Checkbox chọn tất cả -->
              <th class="p-4 w-12 text-center">
                <input
                  type="checkbox"
                  :checked="isAllSelected"
                  @change="toggleSelectAll"
                  :disabled="showtimes.length === 0"
                  class="h-4.5 w-4.5 text-blue-600 border-slate-300 rounded focus:ring-blue-500 transition-colors"
                />
              </th>
              <th class="p-4 text-xs font-bold uppercase tracking-wider text-slate-400">Giờ Chiếu</th>
              <th class="p-4 text-xs font-bold uppercase tracking-wider text-slate-400">Phim</th>
              <th class="p-4 text-xs font-bold uppercase tracking-wider text-slate-400">Vị Trí</th>
              <th class="p-4 text-xs font-bold uppercase tracking-wider text-slate-400 w-52">Tình Trạng Vé</th>
              <th class="p-4 text-xs font-bold uppercase tracking-wider text-slate-400">Trạng Thái</th>
              <th class="p-4 text-xs font-bold uppercase tracking-wider text-slate-400 text-right w-24">Hành Động</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-sm">
            <tr v-if="showtimes.length === 0" class="hover:bg-slate-50/50">
              <td colspan="7" class="p-12 text-center text-slate-400">
                <span class="material-symbols-outlined text-5xl block mb-3 text-slate-300 animate-pulse">event_busy</span>
                <span class="font-medium text-slate-500">Không tìm thấy suất chiếu nào phù hợp.</span>
                <p class="text-xs text-slate-400 mt-1">Hãy thử thay đổi tiêu chí lọc hoặc thêm suất chiếu mới.</p>
              </td>
            </tr>
            <tr v-for="showtime in showtimes" :key="showtime.id" class="hover:bg-slate-50/50 transition-colors">
              <!-- Checkbox cho từng suất chiếu -->
              <td class="p-4 text-center">
                <input
                  type="checkbox"
                  v-model="selectedIds"
                  :value="showtime.id"
                  class="h-4.5 w-4.5 text-blue-600 border-slate-300 rounded focus:ring-blue-500 transition-colors"
                />
              </td>

              <!-- Giờ chiếu (Start - End) -->
              <td class="p-4 whitespace-nowrap">
                <div class="font-extrabold text-slate-900 text-sm flex items-center gap-1.5">
                  <span class="material-symbols-outlined text-slate-400 text-lg">schedule</span>
                  {{ formatTime(showtime.start_time) }} - {{ formatTime(showtime.end_time) }}
                </div>
                <div class="text-[11px] text-slate-400 font-bold uppercase tracking-wider mt-0.5 ml-6">{{ formatDate(showtime.show_date) }}</div>
              </td>

              <!-- Tên phim + Badge độ tuổi -->
              <td class="p-4">
                <div class="flex items-center gap-2">
                  <span class="font-bold text-slate-800 leading-tight max-w-[280px] block truncate" :title="showtime.movie.title">
                    {{ showtime.movie.title }}
                  </span>
                  <span
                    class="px-1.5 py-0.5 text-[10px] font-extrabold tracking-wider border rounded-md uppercase shrink-0"
                    :class="getAgeLimitClass(showtime.movie.age_limit)"
                  >
                    {{ showtime.movie.age_limit || 'K' }}
                  </span>
                </div>
                <div class="text-xs text-slate-400 mt-1 flex items-center gap-1">
                  <span>{{ showtime.movie.duration }} phút</span>
                  <span class="mx-1 text-slate-300">•</span>
                  <span class="truncate max-w-[200px]">{{ showtime.movie.genres ? showtime.movie.genres.join(', ') : 'Hành động' }}</span>
                </div>
              </td>

              <!-- Vị trí (Phòng) -->
              <td class="p-4">
                <div class="font-semibold text-slate-700 text-xs flex items-center gap-1">
                  <span class="material-symbols-outlined text-base text-indigo-500 shrink-0">meeting_room</span>
                  <span class="font-bold">{{ showtime.room.name }}</span>
                </div>
              </td>

              <!-- Tình trạng vé (Progress bar) -->
              <td class="p-4">
                <div class="flex justify-between items-center text-xs font-bold text-slate-600 mb-1">
                  <span>{{ showtime.booked_seats }}/{{ showtime.total_seats }} ghế</span>
                  <span :class="getFillPercentageColor(showtime)">{{ getFillPercentage(showtime) }}%</span>
                </div>
                <!-- Progress Bar -->
                <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                  <div
                    class="h-full rounded-full transition-all duration-500"
                    :class="getProgressBarClass(showtime)"
                    :style="{ width: getFillPercentage(showtime) + '%' }"
                  ></div>
                </div>
              </td>

              <!-- Trạng thái (upcoming/active/running/ended) -->
              <td class="p-4 whitespace-nowrap">
                <span
                  class="px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wider rounded-lg inline-flex items-center gap-1 border"
                  :class="getStatusClass(showtime.status)"
                >
                  <span class="h-1.5 w-1.5 rounded-full" :class="getStatusDotClass(showtime.status)"></span>
                  {{ getStatusText(showtime.status) }}
                </span>
              </td>

              <!-- Hành động -->
              <td class="p-4 text-right">
                <div class="flex items-center justify-end gap-1.5">
                  <a
                    :href="getSeatMapUrl(showtime.id)"
                    class="p-2 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all"
                    title="Xem sơ đồ ghế"
                  >
                    <span class="material-symbols-outlined text-lg">event_seat</span>
                  </a>
                  <button
                    @click="deleteShowtime(showtime)"
                    :disabled="showtime.booked_seats > 0"
                    class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all disabled:opacity-30 disabled:pointer-events-none"
                    title="Hủy/Xóa suất chiếu"
                  >
                    <span class="material-symbols-outlined text-lg">delete</span>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue';
import axios from 'axios';

// Định nghĩa props nhận dữ liệu từ Blade
const props = defineProps({
  cinemas: {
    type: Array,
    required: true,
  },
  movies: {
    type: Array,
    required: true,
  },
  createUrl: {
    type: String,
    default: '/manager/showtimes/create',
  },
  autoGenerateUrl: {
    type: String,
    default: '/manager/showtimes/auto-generate',
  },
  apiRoomsUrl: {
    type: String,
    default: '/api/manager/rooms',
  },
  apiShowtimesUrl: {
    type: String,
    default: '/api/manager/showtimes',
  },
  apiBulkOpenSalesUrl: {
    type: String,
    default: '/api/manager/showtimes/bulk-open-sales',
  },
  apiDeleteShowtimeUrl: {
    type: String,
    default: '/api/manager/showtimes',
  }
});

// --- State ---
const showtimes = ref([]);
const rooms = ref([]);
const selectedIds = ref([]);

const loading = ref(false);
const loadingRooms = ref(false);
const toasts = ref([]);

const filters = reactive({
  cinema_id: props.cinemas.length > 0 ? props.cinemas[0].id : null, // Bắt buộc chọn và mặc định rạp đầu tiên
  room_id: null,
  movie_id: null,
  date: new Date().toISOString().substr(0, 10), // Mặc định hôm nay
});

// --- Toast Notifications ---
const showToast = (message, type = 'success') => {
  const id = Date.now();
  toasts.value.push({ id, message, type });
  setTimeout(() => {
    removeToast(id);
  }, 4000);
};

const removeToast = (id) => {
  toasts.value = toasts.value.filter(t => t.id !== id);
};

// --- Lấy danh sách phòng (Cascading) ---
const fetchRooms = async (cinemaId) => {
  if (!cinemaId) {
    rooms.value = [];
    return;
  }
  loadingRooms.value = true;
  try {
    const response = await axios.get(props.apiRoomsUrl, {
      params: { cinema_id: cinemaId }
    });
    rooms.value = response.data.rooms || response.data;
  } catch (error) {
    console.error('Lỗi tải danh sách phòng chiếu:', error);
    showToast('Không thể tải danh sách phòng chiếu.', 'error');
  } finally {
    loadingRooms.value = false;
  }
};

// --- Lấy danh sách suất chiếu ---
const fetchShowtimes = async () => {
  loading.value = true;
  selectedIds.value = []; // Reset checkbox
  try {
    const response = await axios.get(props.apiShowtimesUrl, {
      params: {
        cinema_id: filters.cinema_id,
        room_id: filters.room_id,
        movie_id: filters.movie_id,
        date: filters.date,
      }
    });
    showtimes.value = response.data.showtimes || response.data;
  } catch (error) {
    console.error('Lỗi khi tải suất chiếu:', error);
    showToast('Lỗi tải dữ liệu suất chiếu từ máy chủ.', 'error');
  } finally {
    loading.value = false;
  }
};

// --- Watchers ---
watch(() => filters.cinema_id, (newCinemaId) => {
  filters.room_id = null; // Reset phòng chiếu khi đổi rạp
  fetchRooms(newCinemaId);
});

watch(filters, () => {
  fetchShowtimes();
}, { deep: true });

// --- Computed ---
const isAllSelected = computed(() => {
  if (showtimes.value.length === 0) return false;
  return showtimes.value.every(s => selectedIds.value.includes(s.id));
});

// Chỉ lấy các suất chiếu được chọn có trạng thái là 'upcoming'
const selectedUpcomingIds = computed(() => {
  return showtimes.value
    .filter(s => selectedIds.value.includes(s.id) && s.status === 'upcoming')
    .map(s => s.id);
});

// --- Actions ---
const toggleSelectAll = (event) => {
  if (event.target.checked) {
    selectedIds.value = showtimes.value.map(s => s.id);
  } else {
    selectedIds.value = [];
  }
};

// Mở bán hàng loạt
const openBulkSales = async () => {
  const idsToOpen = selectedUpcomingIds.value;
  if (idsToOpen.length === 0) return;

  if (!confirm(`Bạn có chắc muốn mở bán ${idsToOpen.length} suất chiếu đã chọn?`)) {
    return;
  }

  loading.value = true;
  try {
    await axios.post(props.apiBulkOpenSalesUrl, { ids: idsToOpen });
    showToast(`Đã mở bán thành công ${idsToOpen.length} suất chiếu.`, 'success');
    fetchShowtimes();
  } catch (error) {
    console.error('Lỗi mở bán hàng loạt:', error);
    showToast('Không thể thực hiện mở bán hàng loạt.', 'error');
  } finally {
    loading.value = false;
  }
};

// Hủy suất chiếu
const deleteShowtime = async (showtime) => {
  if (showtime.booked_seats > 0) {
    showToast('Suất chiếu đã có vé được đặt, không thể xóa!', 'error');
    return;
  }

  if (!confirm(`Bạn có chắc muốn hủy suất chiếu phim "${showtime.movie.title}" lúc ${formatTime(showtime.start_time)}?`)) {
    return;
  }

  loading.value = true;
  try {
    await axios.delete(`${props.apiDeleteShowtimeUrl}/${showtime.id}`);
    showToast('Hủy suất chiếu thành công.', 'success');
    fetchShowtimes();
  } catch (error) {
    console.error('Lỗi khi hủy suất chiếu:', error);
    showToast('Không thể hủy suất chiếu vào lúc này.', 'error');
  } finally {
    loading.value = false;
  }
};

// Reset bộ lọc (Giữ nguyên Rạp chiếu và Ngày chiếu)
const resetFilters = () => {
  filters.room_id = null;
  filters.movie_id = null;
  showToast('Đã đặt lại các tiêu chí lọc phụ.', 'info');
};

// --- Helpers ---
const formatTime = (timeStr) => {
  if (!timeStr) return '';
  const parts = timeStr.split(':');
  if (parts.length >= 2) return `${parts[0]}:${parts[1]}`;
  return timeStr;
};

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  return d.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' });
};

const getFillPercentage = (showtime) => {
  if (!showtime.total_seats) return 0;
  return Math.round((showtime.booked_seats / showtime.total_seats) * 100);
};

const getFillPercentageColor = (showtime) => {
  const percent = getFillPercentage(showtime);
  if (percent >= 80) return 'text-rose-600';
  if (percent >= 50) return 'text-amber-600';
  return 'text-slate-500';
};

const getProgressBarClass = (showtime) => {
  const percent = getFillPercentage(showtime);
  if (percent >= 80) return 'bg-gradient-to-r from-rose-500 to-red-600';
  if (percent >= 50) return 'bg-gradient-to-r from-amber-500 to-orange-500';
  return 'bg-gradient-to-r from-blue-500 to-indigo-500';
};

const getAgeLimitClass = (limit) => {
  const mapping = {
    'P': 'bg-emerald-50 text-emerald-700 border-emerald-300',
    'K': 'bg-blue-50 text-blue-700 border-blue-300',
    'T13': 'bg-amber-50 text-amber-700 border-amber-300',
    'T16': 'bg-orange-50 text-orange-700 border-orange-300',
    'T18': 'bg-rose-50 text-rose-700 border-rose-300',
  };
  return mapping[limit] || 'bg-slate-50 text-slate-700 border-slate-300';
};

const getStatusClass = (status) => {
  const mapping = {
    'upcoming': 'bg-slate-50 text-slate-600 border-slate-200',
    'active': 'bg-emerald-50/50 text-emerald-700 border-emerald-200',
    'showing': 'bg-emerald-50/50 text-emerald-700 border-emerald-200',
    'running': 'bg-rose-50 text-rose-700 border-rose-200',
    'finished': 'bg-slate-800 text-slate-200 border-slate-700',
    'ended': 'bg-slate-800 text-slate-200 border-slate-700',
    'cancelled': 'bg-red-50 text-red-800 border-red-200 line-through',
  };
  return mapping[status] || 'bg-slate-50 text-slate-600 border-slate-200';
};

const getStatusDotClass = (status) => {
  const mapping = {
    'upcoming': 'bg-slate-400',
    'active': 'bg-emerald-500 animate-pulse',
    'showing': 'bg-emerald-500 animate-pulse',
    'running': 'bg-rose-500 animate-pulse',
    'finished': 'bg-slate-400',
    'ended': 'bg-slate-400',
    'cancelled': 'bg-red-500',
  };
  return mapping[status] || 'bg-slate-400';
};

const getStatusText = (status) => {
  const mapping = {
    'upcoming': 'Chờ mở bán',
    'active': 'Đang mở bán',
    'showing': 'Đang mở bán',
    'running': 'Đang chiếu',
    'finished': 'Đã kết thúc',
    'ended': 'Đã kết thúc',
    'cancelled': 'Đã hủy',
  };
  return mapping[status] || status;
};

const getSeatMapUrl = (id) => {
  return `/manager/showtimes/${id}/seats`;
};

// --- Lifecycle ---
onMounted(() => {
  if (filters.cinema_id) {
    fetchRooms(filters.cinema_id);
  }
  fetchShowtimes();
});
</script>

<style scoped>
/* Toast Transiton */
.toast-enter-from {
  opacity: 0;
  transform: translateY(-20px) scale(0.9);
}
.toast-leave-to {
  opacity: 0;
  transform: scale(0.9);
}

/* General Fade Transition */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: scale(0.95);
}
</style>

