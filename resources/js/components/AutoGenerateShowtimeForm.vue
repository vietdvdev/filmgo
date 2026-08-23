<template>
  <div class="bg-white border border-slate-200 shadow-md p-8 rounded-none relative overflow-hidden">
    <!-- Top Accent Bar -->
    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-purple-500 to-indigo-600"></div>

    <!-- Alert / Toast Messages -->
    <div v-if="globalSuccessMessage" class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-none flex items-start gap-3">
      <span class="material-symbols-outlined text-emerald-500 shrink-0">check_circle</span>
      <div class="flex-1 text-sm font-medium">{{ globalSuccessMessage }}</div>
      <button type="button" @click="globalSuccessMessage = ''" class="text-emerald-400 hover:text-emerald-600">
        <span class="material-symbols-outlined text-base">close</span>
      </button>
    </div>

    <div v-if="globalErrorMessage" class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-none flex items-start gap-3">
      <span class="material-symbols-outlined text-rose-500 shrink-0">error</span>
      <div class="flex-1 text-sm font-medium">{{ globalErrorMessage }}</div>
      <button type="button" @click="globalErrorMessage = ''" class="text-rose-400 hover:text-rose-600">
        <span class="material-symbols-outlined text-base">close</span>
      </button>
    </div>

    <!-- Header Section -->
    <div class="border-b border-slate-100 pb-5 mb-6">
      <h3 class="text-lg md:text-xl font-bold tracking-tight text-slate-900 uppercase flex items-center gap-2">
        <span class="material-symbols-outlined text-purple-600 animate-pulse">bolt</span>
        Xếp Lịch Chiếu Tự Động Hàng Loạt
      </h3>
      <p class="text-xs md:text-sm text-slate-500 mt-1">
        Hệ thống sẽ tự động tìm các khung giờ trống trong phòng chiếu để sinh suất chiếu liên tiếp.
      </p>
    </div>

    <!-- Loading cinemas state -->
    <div v-if="isLoadingCinemas" class="py-10 text-center text-slate-400 text-sm flex items-center justify-center gap-2">
      <svg class="animate-spin h-5 w-5 text-purple-500" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
      </svg>
      Đang tải thông tin rạp chiếu...
    </div>

    <form v-else @submit.prevent="submitAutoGenerate" class="space-y-6">

      <!-- Block 1 — Chọn Rạp (hiển thị khi Manager quản lý nhiều rạp) -->
      <div v-if="cinemasList.length > 1" class="bg-purple-50 border border-purple-200/60 p-4">
        <label for="cinema_id" class="block text-xs font-bold uppercase tracking-wider text-purple-800 mb-1">
          🏟️ Chọn Rạp Chiếu
        </label>
        <select
          id="cinema_id"
          v-model="selectedCinemaId"
          class="block w-full px-3 py-2.5 border border-purple-300 text-sm focus:outline-none focus:border-purple-600 focus:ring-1 focus:ring-purple-600 bg-white rounded-none"
        >
          <option value="">-- Chọn rạp chiếu --</option>
          <option v-for="cinema in cinemasList" :key="cinema.id" :value="cinema.id">
            {{ cinema.name }} — {{ cinema.city }}
          </option>
        </select>
        <p class="mt-1 text-xs text-purple-600">Bạn đang quản lý {{ cinemasList.length }} rạp. Hãy chọn rạp muốn xếp lịch.</p>
      </div>

      <!-- Thông báo nếu không có rạp nào -->
      <div v-if="cinemasList.length === 0" class="p-4 bg-amber-50 border border-amber-200 text-amber-800 text-sm rounded-none">
        <span class="font-bold">⚠️ Chưa được phân công rạp chiếu.</span>
        Vui lòng liên hệ Admin để được phân công quản lý rạp trước khi xếp lịch.
      </div>

      <!-- Form chính (chỉ hiện khi đã chọn/có rạp) -->
      <template v-if="cinemasList.length > 0 && (cinemasList.length === 1 || selectedCinemaId)">

        <!-- Block 2 (Base Info — Grid 4 cols) -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <!-- 1. Chọn Phim -->
          <div>
            <label for="movie_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
              1. Chọn Phim
            </label>
            <select
              id="movie_id"
              v-model="payload.movie_id"
              @change="onAutoMovieChange"
              :disabled="isLoadingAutoMovies"
              class="block w-full px-3 py-2.5 border text-sm focus:outline-none focus:border-purple-600 focus:ring-1 focus:ring-purple-600 bg-white rounded-none disabled:bg-slate-100 disabled:text-slate-400"
              :class="errors.movie_id ? 'border-rose-400' : 'border-slate-300'"
            >
              <option value="">{{ isLoadingAutoMovies ? 'Đang tải...' : '-- Chọn phim --' }}</option>
              <option v-for="movie in movies" :key="movie.id" :value="movie.id">
                {{ movie.title }} ({{ movie.duration }} phút)
              </option>
            </select>
            <p v-if="selectedMovie" class="mt-1.5 text-xs text-slate-500">
              Thời lượng: <strong>{{ selectedMovie.duration }} phút</strong>
              &nbsp;•&nbsp; Giới hạn tuổi: <strong>{{ selectedMovie.age_limit || 'T18' }}</strong>
            </p>
            <p v-if="errors.movie_id" class="mt-1 text-xs text-rose-600 font-semibold">{{ errors.movie_id }}</p>
          </div>

          <!-- 2. Chọn Phòng Chiếu -->
          <div>
            <label for="room_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
              2. Phòng Chiếu
            </label>
            <select
              id="room_id"
              v-model="payload.room_id"
              @change="onAutoRoomChange"
              :disabled="!payload.movie_id || isLoadingRooms || roomsList.length === 0"
              class="block w-full px-3 py-2.5 border text-sm focus:outline-none focus:border-purple-600 focus:ring-1 focus:ring-purple-600 bg-white rounded-none disabled:bg-slate-100 disabled:text-slate-400"
              :class="errors.room_id ? 'border-rose-400' : 'border-slate-300'"
            >
              <option value="">{{ !payload.movie_id ? 'Chọn phim trước' : isLoadingRooms ? 'Đang tải phòng...' : (roomsList.length === 0 ? 'Không có phòng phù hợp' : '-- Chọn phòng --') }}</option>
              <option v-for="room in roomsList" :key="room.id" :value="room.id">
                {{ room.room_name }} ({{ room.room_type }} – {{ room.capacity }} ghế)
              </option>
            </select>
            <p v-if="errors.room_id" class="mt-1 text-xs text-rose-600 font-semibold">{{ errors.room_id }}</p>
          </div>

          <!-- 3. Định dạng tự xác định theo Phim và Phòng -->
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
              3. Định Dạng
            </label>
            <div class="min-h-[42px] flex items-center flex-wrap gap-1.5 px-3 py-2.5 border border-slate-300 bg-slate-50 text-sm">
              <span v-if="isLoadingAutoFormats" class="text-slate-400">Đang xác định định dạng...</span>
              <template v-else-if="movieFormats.length">
                <span
                  v-for="format in movieFormats"
                  :key="format.id"
                  class="inline-flex items-center gap-1 px-2.5 py-1 rounded text-[11px] font-bold border"
                  :class="format.id == payload.format_id ? 'bg-purple-100 text-purple-800 border-purple-300' : 'bg-white text-slate-500 border-slate-200'"
                >
                  {{ format.name }}
                  <span v-if="format.surcharge_price > 0" class="font-normal">+{{ Number(format.surcharge_price).toLocaleString() }}đ</span>
                </span>
              </template>
              <span v-else class="text-slate-400">Chọn phim trước</span>
            </div>
            <p v-if="errors.format_id" class="mt-1 text-xs text-rose-600 font-semibold">{{ errors.format_id }}</p>
          </div>

          <!-- 4. Ngày Chiếu -->
          <div>
            <label for="show_date" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
              4. Ngày Chiếu
            </label>
            <input
              id="show_date"
              v-model="payload.show_date"
              type="date"
              :min="todayDate"
              class="block w-full px-3 py-2 border text-sm focus:outline-none focus:border-purple-600 focus:ring-1 focus:ring-purple-600 rounded-none bg-white"
              :class="errors.show_date ? 'border-rose-400' : 'border-slate-300'"
            />
            <p v-if="errors.show_date" class="mt-1 text-xs text-rose-600 font-semibold">{{ errors.show_date }}</p>
          </div>
        </div>

        <!-- Block 3 (Shift Configuration — bg-gray-50) -->
        <div class="bg-gray-50 border border-slate-200/80 p-5 space-y-4">
          <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 border-b border-slate-200 pb-2">
            ⏰ Cấu hình ca làm việc
          </h4>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Giờ mở ca -->
            <div>
              <label for="shift_start" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                Giờ Mở Ca
              </label>
              <input
                id="shift_start"
                v-model="payload.shift_start"
                type="time"
                class="block w-full px-3 py-2 border text-sm focus:outline-none focus:border-purple-600 focus:ring-1 focus:ring-purple-600 rounded-none bg-white"
                :class="errors.shift_start ? 'border-rose-400' : 'border-slate-300'"
              />
              <p v-if="errors.shift_start" class="mt-1 text-xs text-rose-600 font-semibold">{{ errors.shift_start }}</p>
            </div>

            <!-- Giờ đóng ca -->
            <div>
              <label for="shift_end" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                Giờ Đóng Ca
              </label>
              <input
                id="shift_end"
                v-model="payload.shift_end"
                type="time"
                class="block w-full px-3 py-2 border text-sm focus:outline-none focus:border-purple-600 focus:ring-1 focus:ring-purple-600 rounded-none bg-white"
                :class="errors.shift_end ? 'border-rose-400' : 'border-slate-300'"
              />
              <p v-if="errors.shift_end" class="mt-1 text-xs text-rose-600 font-semibold">{{ errors.shift_end }}</p>
            </div>

            <!-- Thời gian dọn rạp -->
            <div>
              <label for="cleaning_time" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                Thời gian dọn dẹp (Phút)
              </label>
              <input
                id="cleaning_time"
                v-model.number="payload.cleaning_time"
                type="number"
                min="0"
                max="60"
                class="block w-full px-3 py-2 border text-sm focus:outline-none focus:border-purple-600 focus:ring-1 focus:ring-purple-600 rounded-none bg-white"
                :class="errors.cleaning_time ? 'border-rose-400' : 'border-slate-300'"
              />
              <p v-if="errors.cleaning_time" class="mt-1 text-xs text-rose-600 font-semibold">{{ errors.cleaning_time }}</p>
            </div>
          </div>
        </div>

        <!-- Block 4 (Financial Config) -->
        <div>
          <label for="standard_price" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
            💰 Giá Vé Tiêu Chuẩn (VNĐ)
          </label>
          <input
            id="standard_price"
            v-model.number="payload.standard_price"
            type="number"
            min="0"
            step="1000"
            class="block w-full px-3 py-2 border text-sm focus:outline-none focus:border-purple-600 focus:ring-1 focus:ring-purple-600 rounded-none bg-white"
            :class="errors.standard_price ? 'border-rose-400' : 'border-slate-300'"
          />
          <div class="flex gap-2 mt-2">
            <button type="button" @click="setPrice(80000)"
              class="text-xs px-2.5 py-1 bg-slate-100 text-slate-600 hover:bg-purple-100 hover:text-purple-700 transition-colors rounded-none border border-slate-200">80.000đ</button>
            <button type="button" @click="setPrice(100000)"
              class="text-xs px-2.5 py-1 bg-slate-100 text-slate-600 hover:bg-purple-100 hover:text-purple-700 transition-colors rounded-none border border-slate-200">100.000đ</button>
            <button type="button" @click="setPrice(120000)"
              class="text-xs px-2.5 py-1 bg-slate-100 text-slate-600 hover:bg-purple-100 hover:text-purple-700 transition-colors rounded-none border border-slate-200">120.000đ</button>
            <button type="button" @click="setPrice(150000)"
              class="text-xs px-2.5 py-1 bg-slate-100 text-slate-600 hover:bg-purple-100 hover:text-purple-700 transition-colors rounded-none border border-slate-200">150.000đ</button>
          </div>
          <p class="mt-1 text-[10px] text-slate-400">Hệ thống sẽ tự động cộng thêm phụ thu ngày lễ và quy tắc giờ cao điểm.</p>
          <p v-if="roomSurcharge > 0" class="mt-1 text-xs font-semibold text-purple-700">
            Phụ thu phòng {{ selectedRoom?.room_type }}: +{{ roomSurcharge.toLocaleString() }}đ
            <span class="font-normal text-slate-400">• Giá dự kiến tối thiểu: {{ estimatedPrice.toLocaleString() }}đ</span>
          </p>
          <p v-if="errors.standard_price" class="mt-1 text-xs text-rose-600 font-semibold">{{ errors.standard_price }}</p>
        </div>

        <!-- Cấu Hình Mở Bán -->
        <div class="space-y-3">
          <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 border-b border-slate-200 pb-2">Cấu Hình Mở Bán</h4>
          <div class="bg-blue-50/30 border border-blue-100 p-5 space-y-4">
            <label class="flex items-center gap-2.5 cursor-pointer select-none w-max">
              <input type="checkbox" v-model="isScheduled" @change="handleScheduleToggle"
                class="w-4 h-4 text-purple-600 border-slate-300 rounded focus:ring-purple-500">
              <span class="text-sm font-bold uppercase tracking-wider text-slate-700">Hẹn giờ mở bán tự động</span>
            </label>
            <div v-if="isScheduled" class="space-y-3 pl-6 border-l-2 border-purple-300 ml-1">
              <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Thời gian mở bán</label>
                <input v-model="publishAt" type="datetime-local"
                  class="block w-full md:w-1/2 px-3 py-2 border border-slate-300 text-sm focus:outline-none focus:border-purple-600 focus:ring-1 focus:ring-purple-600 rounded-none bg-white">
                <p class="mt-1 text-xs text-slate-400">Các suất chiếu sinh ra sẽ được lên lịch mở bán cùng thời điểm này.</p>
              </div>
              <div class="flex flex-wrap gap-2">
                <button type="button" @click="setScheduleNow"
                  class="px-2.5 py-1 bg-white border border-slate-300 hover:bg-purple-400 hover:text-purple-600 text-slate-600 text-xs font-semibold rounded-none transition-colors">
                  ⚡ Ngay bây giờ
                </button>
                <button type="button" @click="setScheduleTomorrow"
                  class="px-2.5 py-1 bg-white border border-slate-300 hover:bg-purple-400 hover:text-purple-600 text-slate-600 text-xs font-semibold rounded-none transition-colors">
                  🌅 09:00 Sáng mai
                </button>
                <button type="button" @click="setSchedule24hBefore"
                  class="px-2.5 py-1 bg-white border border-slate-300 hover:bg-purple-400 hover:text-purple-600 text-slate-600 text-xs font-semibold rounded-none transition-colors">
                  🕐 Trước giờ mở ca 24h
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Action Bar -->
        <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
          <button
            type="button"
            @click="cancelAutoGenerate"
            :disabled="isLoading"
            class="px-5 py-2.5 border border-slate-300 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition-colors rounded-none disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Hủy
          </button>
          <button
            type="submit"
            :disabled="isLoading || roomsList.length === 0"
            class="px-5 py-2.5 bg-purple-600 text-white text-sm font-semibold hover:bg-purple-700 disabled:bg-slate-300 disabled:cursor-not-allowed transition-colors flex items-center gap-2 rounded-none"
          >
            <svg v-if="isLoading" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            {{ isLoading ? 'Đang xử lý...' : '⚡ Bắt Đầu Xếp Lịch' }}
          </button>
        </div>

      </template>

    </form>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
  myCinemasUrl: {
    type: String,
    default: '/manager/api/admin/my-cinemas'
  },
  roomsUrlPattern: {
    type: String,
    default: '/manager/api/admin/cinemas/:cinema_id/rooms'
  },
  roomsByMovieUrlPattern: {
    type: String,
    default: '/manager/showtimes/api/rooms-by-movie/:movie_id'
  },
  formatsByMovieUrlPattern: {
    type: String,
    default: '/manager/showtimes/api/formats-by-movie/:movie_id'
  },
  intersectionFormatsUrlPattern: {
    type: String,
    default: '/api/rooms/:room_id/movies/:movie_id/formats'
  },
  autoGenerateUrl: {
    type: String,
    default: '/manager/showtimes/api/auto-generate'
  },
  cancelUrl: {
    type: String,
    default: '/manager/showtimes'
  }
});

const { movies, csrfToken } = window.__SHOWTIME_DATA__ || { movies: [], csrfToken: '' };

axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
axios.defaults.headers.common['Accept']       = 'application/json';

const todayDate = new Date().toISOString().split('T')[0];

const payload = reactive({
  room_id: '',
  movie_id: '',
  format_id: '',
  show_date: todayDate,
  shift_start: '08:00',
  shift_end: '23:00',
  cleaning_time: 20,
  standard_price: 80000
});

// ── Cấu hình mở bán ─────────────────────────────────────
const isScheduled = ref(false);
const publishAt   = ref('');

const padZero2 = n => String(n).padStart(2, '0');
const fmtDTL = (d) =>
  `${d.getFullYear()}-${padZero2(d.getMonth()+1)}-${padZero2(d.getDate())}T${padZero2(d.getHours())}:${padZero2(d.getMinutes())}`;

const handleScheduleToggle = () => {
  if (!isScheduled.value) { publishAt.value = ''; }
  else if (!publishAt.value) { setScheduleNow(); }
};
const setScheduleNow = () => { publishAt.value = fmtDTL(new Date()); };
const setScheduleTomorrow = () => {
  const d = new Date(); d.setDate(d.getDate() + 1); d.setHours(9, 0, 0, 0);
  publishAt.value = fmtDTL(d);
};
const setSchedule24hBefore = () => {
  if (!payload.show_date || !payload.shift_start) {
    globalErrorMessage.value = 'Vui lòng chọn Ngày chiếu và Giờ mở ca trước.'; return;
  }
  const d = new Date(`${payload.show_date}T${payload.shift_start}:00`);
  d.setHours(d.getHours() - 24);
  publishAt.value = fmtDTL(d);
};

const isLoading           = ref(false);
const isLoadingCinemas    = ref(true);
const isLoadingRooms      = ref(false);
const isLoadingAutoMovies = ref(false);
const isLoadingAutoFormats= ref(false);
const errors              = ref({});
const globalSuccessMessage= ref('');
const globalErrorMessage  = ref('');

const cinemasList    = ref([]);
const roomsList      = ref([]);
const movieFormats    = ref([]);
const compatibleFormats = ref([]);

const selectedCinemaId = ref('');
let movieChangeRequest = 0;
let roomChangeRequest = 0;

const selectedMovie = computed(() => movies.find(movie => movie.id == payload.movie_id) || null);
const selectedRoom = computed(() => roomsList.value.find(room => room.id == payload.room_id) || null);
const roomSurcharge = computed(() => Number(selectedRoom.value?.room_surcharge || 0));
const estimatedPrice = computed(() => Number(payload.standard_price || 0) + roomSurcharge.value);

onMounted(async () => {
  await fetchCinemas();
});

const fetchCinemas = async () => {
  isLoadingCinemas.value = true;
  try {
    const res = await axios.get(props.myCinemasUrl);
    cinemasList.value = res.data || [];
    if (cinemasList.value.length === 1) {
      selectedCinemaId.value = cinemasList.value[0].id;
      await fetchRoomsByCinema(selectedCinemaId.value);
    }
  } catch (e) {
    globalErrorMessage.value = 'Không thể tải danh sách rạp chiếu. Vui lòng tải lại trang.';
  } finally {
    isLoadingCinemas.value = false;
  }
};

const fetchRoomsByCinema = async (cinemaId) => {
  movieChangeRequest++;
  roomChangeRequest++;
  if (!cinemaId) {
    roomsList.value = [];
    payload.room_id = '';
    payload.movie_id = '';
    payload.format_id = '';
    return;
  }
  isLoadingRooms.value = true;
  roomsList.value = [];
  payload.room_id = '';
  payload.movie_id = '';
  payload.format_id = '';
  movieFormats.value = [];
  compatibleFormats.value = [];
  try {
    const url = props.roomsUrlPattern.replace(':cinema_id', cinemaId);
    const res = await axios.get(url);
    roomsList.value = res.data || [];
  } catch (e) {
    globalErrorMessage.value = 'Không thể tải danh sách phòng chiếu của rạp này.';
  } finally {
    isLoadingRooms.value = false;
  }
};

const onAutoRoomChange = async () => {
  const requestId = ++roomChangeRequest;
  payload.format_id = '';
  compatibleFormats.value = [];

  if (!payload.room_id) return;

  isLoadingAutoFormats.value = true;
  try {
    const url = props.intersectionFormatsUrlPattern
      .replace(':room_id', payload.room_id)
      .replace(':movie_id', payload.movie_id);
    const res = await axios.get(url);
    if (requestId !== roomChangeRequest) return;

    compatibleFormats.value = res.data.data || res.data || [];
    if (compatibleFormats.value.length > 0) {
      // Ưu tiên format trùng loại phòng, giống luồng tạo suất thủ công.
      const selectedRoom = roomsList.value.find(room => room.id == payload.room_id);
      const roomType = selectedRoom?.room_type?.toUpperCase() || '';
      const matchedFormat = compatibleFormats.value.find(
        format => format.name?.toUpperCase() === roomType
      );
      payload.format_id = (matchedFormat || compatibleFormats.value[0]).id;
    }
  } catch (e) {
    if (requestId === roomChangeRequest) {
      globalErrorMessage.value = 'Không thể xác định định dạng chiếu của phòng và phim.';
    }
  } finally {
    if (requestId === roomChangeRequest) isLoadingAutoFormats.value = false;
  }
};

const onAutoMovieChange = async () => {
  const requestId = ++movieChangeRequest;
  roomChangeRequest++;
  payload.room_id = '';
  payload.format_id = '';
  roomsList.value = [];
  movieFormats.value = [];
  compatibleFormats.value = [];

  if (!payload.movie_id || !selectedCinemaId.value) return;

  isLoadingRooms.value = true;
  isLoadingAutoFormats.value = true;
  try {
    const roomsUrl = props.roomsByMovieUrlPattern.replace(':movie_id', payload.movie_id);
    const formatsUrl = props.formatsByMovieUrlPattern.replace(':movie_id', payload.movie_id);
    const [roomsResponse, formatsResponse] = await Promise.all([
      axios.get(roomsUrl),
      axios.get(formatsUrl),
    ]);
    if (requestId !== movieChangeRequest || payload.movie_id === '') return;

    roomsList.value = (roomsResponse.data.data || []).filter(room => room.cinema_id == selectedCinemaId.value);
    movieFormats.value = formatsResponse.data.data || formatsResponse.data || [];
  } catch (e) {
    if (requestId === movieChangeRequest) {
      globalErrorMessage.value = 'Không thể tải phòng hoặc định dạng của phim.';
    }
  } finally {
    if (requestId === movieChangeRequest) {
      isLoadingRooms.value = false;
      isLoadingAutoFormats.value = false;
    }
  }
};

watch(selectedCinemaId, async (newCinemaId) => {
  await fetchRoomsByCinema(newCinemaId);
});

const setPrice = (amount) => {
  payload.standard_price = amount;
};

const submitAutoGenerate = async () => {
  errors.value = {};
  globalSuccessMessage.value = '';
  globalErrorMessage.value = '';

  if (!payload.movie_id) {
    errors.value.movie_id = 'Vui lòng chọn phim.';
  }
  if (!payload.room_id) {
    errors.value.room_id = 'Vui lòng chọn phòng chiếu.';
  }
  if (!payload.show_date) {
    errors.value.show_date = 'Vui lòng chọn ngày chiếu.';
  } else if (payload.show_date < todayDate) {
    errors.value.show_date = 'Ngày chiếu không được là ngày trong quá khứ.';
  }
  if (payload.shift_start >= payload.shift_end) {
    errors.value.shift_end = 'Giờ đóng ca phải sau giờ mở ca.';
  }
  if (payload.cleaning_time < 0) {
    errors.value.cleaning_time = 'Thời gian dọn dẹp không được âm.';
  }
  if (payload.standard_price < 0) {
    errors.value.standard_price = 'Giá vé không được âm.';
  }

  if (Object.keys(errors.value).length > 0) {
    globalErrorMessage.value = 'Vui lòng kiểm tra lại thông tin các trường lỗi.';
    return;
  }

  isLoading.value = true;

  try {
    const res = await axios.post(props.autoGenerateUrl, {
      ...payload,
      publish_at: isScheduled.value ? (publishAt.value || null) : null
    });

    if (res.data && res.data.success) {
      sessionStorage.setItem('showtime_success_message', `Đã tự động xếp thành công ${res.data.total_generated} suất chiếu mới cho ngày ${payload.show_date}!`);
      window.location.href = `${props.cancelUrl}?date=${payload.show_date}`;
    }
  } catch (error) {
    const status = error.response ? error.response.status : null;
    const data   = error.response ? error.response.data : null;

    if (status === 422 && data && data.errors) {
      Object.entries(data.errors).forEach(([field, msg]) => {
        errors.value[field] = Array.isArray(msg) ? msg[0] : msg;
      });
      globalErrorMessage.value = 'Vui lòng kiểm tra lại thông tin các trường lỗi.';
    } else if (status === 403) {
      globalErrorMessage.value = data && data.message ? data.message : 'Bạn không có quyền thực hiện thao tác này.';
    } else if (status === 400 && data && data.message) {
      globalErrorMessage.value = data.message;
    } else if (data && data.message) {
      globalErrorMessage.value = data.message;
    } else {
      globalErrorMessage.value = 'Đã xảy ra lỗi hệ thống. Vui lòng thử lại sau.';
    }
  } finally {
    isLoading.value = false;
  }
};

const cancelAutoGenerate = () => {
  window.location.href = props.cancelUrl;
};
</script>
