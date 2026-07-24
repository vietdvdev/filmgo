<template>
  <div>
    <!-- Toasts -->
    <div class="fixed top-4 right-4 z-50 space-y-2 pointer-events-none">
      <transition-group name="toast-fade">
        <div
          v-for="t in toasts"
          :key="t.id"
          class="flex items-center gap-3 p-4 rounded-lg shadow-xl border text-sm max-w-sm pointer-events-auto bg-white"
          :class="t.type === 'success'
            ? 'bg-emerald-50 border-emerald-200 text-emerald-800'
            : 'bg-rose-50 border-rose-200 text-rose-800'">
          <span
            class="material-symbols-outlined shrink-0"
            :class="t.type === 'success' ? 'text-emerald-500' : 'text-rose-500'">
            {{ t.type === 'success' ? 'check_circle' : 'error' }}
          </span>
          <span class="flex-1 font-medium">{{ t.message }}</span>
          <button type="button" @click="removeToast(t.id)" class="text-slate-400 hover:text-slate-600 shrink-0">
            <span class="material-symbols-outlined text-base">close</span>
          </button>
        </div>
      </transition-group>
    </div>

    <!-- Form Card -->
    <div class="bg-white border border-slate-200 shadow-sm p-8 rounded-none relative overflow-hidden">
      <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500 to-indigo-600"></div>

      <form @submit.prevent="submitForm" class="space-y-6">

        <!-- Phim -->
        <div>
          <label for="movie_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
            Chọn Phim
          </label>
          <select id="movie_id" v-model="form.movie_id" required @change="onMovieChange"
            class="block w-full px-3 py-2.5 border border-slate-300 text-sm focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 bg-white">
            <option value="">-- Chọn phim --</option>
            <option v-for="m in movies" :key="m.id" :value="m.id">
              {{ m.title }} ({{ m.duration }} phút)
            </option>
          </select>
          <p v-if="selectedMovie" class="mt-1.5 text-xs text-slate-500">
            Thời lượng: <strong>{{ selectedMovie.duration }} phút</strong>
            &nbsp;•&nbsp;
            Giới hạn tuổi: <strong>{{ selectedMovie.age_limit || 'T18' }}</strong>
          </p>
          <p v-if="fieldErrors.movie_id" class="mt-1 text-xs text-red-600 font-semibold">
            {{ fieldErrors.movie_id }}
          </p>
        </div>

        <!-- Định dạng chiếu -->
        <div>
          <label for="format_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
            Định Dạng Chiếu
          </label>
          <select id="format_id" v-model="form.format_id" required @change="onFormatChange"
            :disabled="!form.movie_id || loadingFormats"
            class="block w-full px-3 py-2.5 border border-slate-300 text-sm focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 bg-white disabled:bg-slate-100 disabled:text-slate-400">
            <option value="">
              -- {{ !form.movie_id ? 'Chọn phim trước' : loadingFormats ? 'Đang tải định dạng...' : 'Chọn định dạng chiếu' }} --
            </option>
            <option v-for="f in formats" :key="f.id" :value="f.id">
              {{ f.name }}
            </option>
          </select>
          <p v-if="fieldErrors.format_id" class="mt-1 text-xs text-red-600 font-semibold">
            {{ fieldErrors.format_id }}
          </p>
        </div>

        <!-- Rạp chiếu -->
        <div>
          <label for="cinema_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
            Chọn Rạp Chiếu
          </label>
          <select id="cinema_id" v-model="form.cinema_id" required @change="onCinemaChange"
            :disabled="loadingCinemas"
            class="block w-full px-3 py-2.5 border border-slate-300 text-sm focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 bg-white disabled:bg-slate-100 disabled:text-slate-400">
            <option value="">-- {{ loadingCinemas ? 'Đang tải danh sách rạp...' : 'Chọn rạp chiếu' }} --</option>
            <option v-for="c in cinemas" :key="c.id" :value="c.id">
              {{ c.name }}
            </option>
          </select>
          <p v-if="fieldErrors.cinema_id" class="mt-1 text-xs text-red-600 font-semibold">
            {{ fieldErrors.cinema_id }}
          </p>
        </div>

        <!-- Phòng -->
        <div>
          <label for="room_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
            Chọn Phòng Chiếu
          </label>
          <select id="room_id" v-model="form.room_id" required @change="triggerOverlapCheck"
            :disabled="!form.cinema_id || !form.format_id || loadingRooms"
            class="block w-full px-3 py-2.5 border border-slate-300 text-sm focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 bg-white disabled:bg-slate-100 disabled:text-slate-400">
            <option value="">
              -- {{ loadingRooms ? 'Đang tải danh sách phòng...' : (!form.cinema_id || !form.format_id) ? 'Chọn rạp và định dạng trước' : 'Chọn phòng chiếu' }} --
            </option>
            <option v-for="r in rooms" :key="r.id" :value="r.id">
              {{ r.room_name }} ({{ r.room_type }} – {{ r.capacity }} ghế)
            </option>
          </select>
          <p v-if="rooms.length === 0 && form.cinema_id && form.format_id && !loadingRooms" class="mt-1 text-xs text-amber-600 font-semibold">
            Không có phòng nào hỗ trợ định dạng chiếu này tại rạp đã chọn.
          </p>
          <p v-if="fieldErrors.room_id" class="mt-1 text-xs text-red-600 font-semibold">
            {{ fieldErrors.room_id }}
          </p>
        </div>

        <!-- Ngày & Giờ -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label for="show_date" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
              Ngày Chiếu
            </label>
            <input id="show_date" v-model="form.show_date" type="date" required :min="today"
              @change="onDateOrTimeChange"
              class="block w-full px-3 py-2 border border-slate-300 text-sm focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
            <p v-if="fieldErrors.show_date" class="mt-1 text-xs text-red-600 font-semibold">
              {{ fieldErrors.show_date }}
            </p>
          </div>
          <div>
            <label for="start_time" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
              Giờ Bắt Đầu
            </label>
            <input id="start_time" v-model="form.start_time" type="time" required
              @change="onDateOrTimeChange"
              class="block w-full px-3 py-2 border border-slate-300 text-sm focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
            <p v-if="fieldErrors.start_time" class="mt-1 text-xs text-red-600 font-semibold">
              {{ fieldErrors.start_time }}
            </p>
          </div>
        </div>

        <!-- Zeit & Overlap -->
        <div class="p-4 bg-slate-50 border border-slate-200 space-y-2">
          <div class="flex justify-between items-center text-sm">
            <span class="text-slate-500 font-medium flex items-center gap-1.5">
              <span class="material-symbols-outlined" style="font-size:16px">update</span>
              Giờ kết thúc dự kiến:
            </span>
            <span :class="computedEndTime ? 'font-bold text-slate-800' : 'text-slate-400 italic'">
              {{ computedEndTime || '— chưa đủ thông tin —' }}
            </span>
          </div>
          <div v-if="checkingOverlap" class="flex gap-2 items-center text-blue-600 text-xs font-semibold">
            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            Đang kiểm tra lịch chiếu...
          </div>
          <div v-if="overlapError && !checkingOverlap"
            class="flex gap-2 items-start text-rose-700 text-xs font-semibold p-3 bg-rose-50 border border-rose-200">
            <span class="material-symbols-outlined text-base shrink-0">warning</span>
            {{ overlapError }}
          </div>
          <div v-if="overlapOk && !checkingOverlap && !overlapError"
            class="flex gap-2 items-center text-emerald-700 text-xs font-semibold p-2 bg-emerald-50 border border-emerald-200">
            <span class="material-symbols-outlined text-base">check_circle</span>
            Khung giờ trống, có thể xếp lịch.
          </div>
        </div>

        <!-- Cấu hình giá vé (Strict Auto-calculation) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <!-- Ô 1: GIÁ TIÊU CHUẨN -->
          <div class="bg-slate-50 border border-slate-200 p-4 flex flex-col justify-between">
            <div>
              <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">1. GIÁ TIÊU CHUẨN</h4>
              <input v-model.number="standardPrice" type="number" required min="0" step="1000"
                class="block w-full px-3 py-2 border border-slate-300 text-sm focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 bg-white">
              <div class="flex gap-1.5 mt-2">
                <button type="button" @click="setPrice(80000)"
                  class="text-[10px] px-2 py-0.5 bg-slate-200 text-slate-700 hover:bg-slate-300 transition-colors">80K</button>
                <button type="button" @click="setPrice(100000)"
                  class="text-[10px] px-2 py-0.5 bg-slate-200 text-slate-700 hover:bg-slate-300 transition-colors">100K</button>
                <button type="button" @click="setPrice(120000)"
                  class="text-[10px] px-2 py-0.5 bg-slate-200 text-slate-700 hover:bg-slate-300 transition-colors">120K</button>
              </div>
            </div>
            <p v-if="fieldErrors.base_price" class="mt-1 text-xs text-red-600 font-semibold">
              {{ fieldErrors.base_price }}
            </p>
          </div>

          <!-- Ô 2: PHỤ THU NGÀY/GIỜ -->
          <div class="bg-amber-50/50 border border-amber-200/70 p-4 flex flex-col justify-between">
            <div>
              <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">2. PHỤ THU NGÀY/GIỜ</h4>
              <div class="text-lg font-bold text-amber-800">
                + {{ surchargeAmt.toLocaleString() }} ₫
              </div>
              <p v-if="priceReason" class="text-[11px] text-amber-700 mt-1 italic leading-tight">
                Reason: {{ priceReason }}
              </p>
              <p v-else class="text-[11px] text-slate-400 mt-1 italic">
                Không có phụ thu ngày thường
              </p>
            </div>
          </div>

          <!-- Ô 3: GIÁ THỰC TẾ -->
          <div class="bg-blue-50 border border-blue-200 p-4 flex flex-col justify-between">
            <div>
              <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">3. GIÁ THỰC TẾ</h4>
              <div class="text-xl font-black text-blue-800">
                {{ computedActualPrice.toLocaleString() }} ₫
              </div>
              <p class="text-[10px] text-blue-600 mt-1">
                (Bằng giá tiêu chuẩn + phụ thu)
              </p>
            </div>
          </div>
        </div>

        <!-- Cấu Hình Mở Bán -->
        <div class="space-y-3">
          <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 border-b border-slate-100 pb-2">Cấu Hình Mở Bán</h3>
          <div class="p-5 bg-slate-50 border border-slate-200 space-y-4">
            <label class="flex items-center gap-2.5 cursor-pointer select-none w-max">
              <input type="checkbox" v-model="isScheduled" @change="handleScheduleToggle"
                class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
              <span class="text-sm font-bold uppercase tracking-wider text-slate-700">Hẹn giờ mở bán tự động</span>
            </label>
            <transition name="mo-ban-fade">
              <div v-if="isScheduled" class="space-y-3 pl-6 border-l-2 border-blue-300 ml-1">
                <div>
                  <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Thời gian mở bán</label>
                  <input v-model="publishAt" type="datetime-local"
                    class="block w-full md:w-1/2 px-3 py-2 border border-slate-300 text-sm focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 bg-white">
                  <p class="mt-1 text-xs text-slate-400">Hệ thống sẽ tự động mở bán đúng giờ bạn chọn.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                  <button type="button" @click="setScheduleNow"
                    class="px-2.5 py-1 bg-white border border-slate-300 hover:border-blue-400 hover:text-blue-600 text-slate-600 text-xs font-semibold transition-colors">
                    ⚡ Ngay bây giờ
                  </button>
                  <button type="button" @click="setScheduleTomorrow"
                    class="px-2.5 py-1 bg-white border border-slate-300 hover:border-blue-400 hover:text-blue-600 text-slate-600 text-xs font-semibold transition-colors">
                    🌅 09:00 Sáng mai
                  </button>
                  <button type="button" @click="setSchedule24hBefore"
                    class="px-2.5 py-1 bg-white border border-slate-300 hover:border-blue-400 hover:text-blue-600 text-slate-600 text-xs font-semibold transition-colors">
                    🕐 Trước giờ chiếu 24h
                  </button>
                </div>
              </div>
            </transition>
          </div>
        </div>

        <!-- Buttons -->
        <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
          <a :href="urls.cancel"
            class="px-5 py-2.5 border border-slate-300 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition-colors">
            Hủy bỏ
          </a>
          <button type="submit" :disabled="submitting || !!overlapError"
            class="px-5 py-2.5 bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 disabled:bg-slate-300 disabled:cursor-not-allowed transition-colors flex items-center gap-2">
            <svg v-if="submitting" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            {{ submitting ? 'Đang xử lý...' : 'Lên Lịch Suất Chiếu' }}
          </button>
        </div>

      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import axios from 'axios';

// Get preloaded data
const { movies, csrfToken, urls } = window.__SHOWTIME_DATA__ || {
  movies: [],
  csrfToken: '',
  urls: {}
};

axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
axios.defaults.headers.common['Accept']       = 'application/json';

const today = new Date().toISOString().split('T')[0];

// Returns current time as 'HH:MM' string in local timezone
const nowTimeLocal = () => {
  const d = new Date();
  return String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');
};

// Returns true if the selected show_date + start_time is in the past
const isDatetimePast = (showDate, startTime) => {
  if (!showDate || !startTime) return false;
  const selected = new Date(`${showDate}T${startTime}:00`);
  return selected < new Date();
};

const form = reactive({
  movie_id:   '',
  format_id:  '',
  cinema_id:  '',
  room_id:    '',
  show_date:  today,
  start_time: '',
  base_price: 80000,
});

const cinemas         = ref([]);
const formats         = ref([]);
const rooms           = ref([]);
const fieldErrors     = reactive({});
const checkingOverlap = ref(false);
const loadingCinemas  = ref(false);
const loadingFormats  = ref(false);
const loadingRooms    = ref(false);
const overlapError    = ref('');
const overlapOk       = ref(false);
const priceReason     = ref('');
const submitting      = ref(false);
const toasts          = ref([]);

// ── Cấu hình mở bán ─────────────────────────────────────────
const isScheduled  = ref(false);
const publishAt    = ref('');

const padZero = n => String(n).padStart(2, '0');
const formatDateTimeLocal = (d) => {
  return `${d.getFullYear()}-${padZero(d.getMonth()+1)}-${padZero(d.getDate())}T${padZero(d.getHours())}:${padZero(d.getMinutes())}`;
};

const handleScheduleToggle = () => {
  if (!isScheduled.value) {
    publishAt.value = '';
  } else if (!publishAt.value) {
    setScheduleNow();
  }
};
const setScheduleNow = () => { publishAt.value = formatDateTimeLocal(new Date()); };
const setScheduleTomorrow = () => {
  const d = new Date(); d.setDate(d.getDate() + 1); d.setHours(9, 0, 0, 0);
  publishAt.value = formatDateTimeLocal(d);
};
const setSchedule24hBefore = () => {
  if (!form.show_date || !form.start_time) {
    addToast('Vui lòng chọn Ngày chiếu và Giờ chiếu trước.', 'error'); return;
  }
  const sd = new Date(`${form.show_date}T${form.start_time}:00`);
  sd.setHours(sd.getHours() - 24);
  publishAt.value = formatDateTimeLocal(sd);
};

let overlapTimer = null;
let priceTimer   = null;

// Derived properties
const selectedMovie = computed(() =>
  movies.find(m => m.id == form.movie_id) || null
);

const computedEndTime = computed(() => {
  if (!selectedMovie.value || !form.start_time) return '';
  const [h, m]  = form.start_time.split(':').map(Number);
  const total   = h * 60 + m + selectedMovie.value.duration;
  const eh      = Math.floor(total / 60) % 24;
  const em      = total % 60;
  return String(eh).padStart(2, '0') + ':' + String(em).padStart(2, '0');
});

// Toasts
const addToast = (message, type) => {
  const id = Date.now();
  toasts.value.push({ id, message, type: type || 'success' });
  setTimeout(() => removeToast(id), 5000);
};
const removeToast = id => {
  toasts.value = toasts.value.filter(t => t.id !== id);
};

const standardPrice = ref(80000);
const surchargeAmt  = ref(0);

const computedActualPrice = computed(() => {
  return Number(standardPrice.value || 0) + Number(surchargeAmt.value || 0);
});

// Load cinemas
const fetchCinemas = async () => {
  loadingCinemas.value = true;
  try {
    const res = await axios.get(urls.myCinemas);
    cinemas.value = res.data;
  } catch (e) {
    addToast('Không thể tải danh sách rạp chiếu.', 'error');
  } finally {
    loadingCinemas.value = false;
  }
};

// Fetch formats by movie
const fetchFormats = async () => {
  formats.value = [];
  form.format_id = '';
  form.room_id = '';
  rooms.value = [];
  overlapError.value = '';
  overlapOk.value = false;

  if (!form.movie_id) return;

  loadingFormats.value = true;
  try {
    const url = urls.formatsByMovie.replace(':movie_id', form.movie_id);
    const res = await axios.get(url);
    formats.value = res.data;
    if (formats.value.length === 1) {
      form.format_id = formats.value[0].id;
      await fetchCompatibleRooms();
    }
  } catch (e) {
    addToast('Không thể tải danh sách định dạng chiếu.', 'error');
  } finally {
    loadingFormats.value = false;
  }
};

// Fetch compatible rooms by cinema + format
const fetchCompatibleRooms = async () => {
  rooms.value = [];
  form.room_id = '';
  overlapError.value = '';
  overlapOk.value = false;

  if (!form.cinema_id || !form.format_id) return;

  loadingRooms.value = true;
  try {
    const res = await axios.get(urls.compatibleRooms, {
      params: { cinema_id: form.cinema_id, format_id: form.format_id }
    });
    rooms.value = res.data;
  } catch (e) {
    addToast('Không thể tải danh sách phòng chiếu phù hợp.', 'error');
  } finally {
    loadingRooms.value = false;
  }
};

// Cinema Change Handler
const onCinemaChange = async () => {
  form.room_id = '';
  rooms.value = [];
  overlapError.value = '';
  overlapOk.value = false;

  if (!form.cinema_id || !form.format_id) return;
  await fetchCompatibleRooms();
};

// Format Change Handler
const onFormatChange = async () => {
  form.room_id = '';
  rooms.value = [];
  overlapError.value = '';
  overlapOk.value = false;

  if (!form.cinema_id || !form.format_id) return;
  await fetchCompatibleRooms();
};

// Overlap check
const triggerOverlapCheck = () => {
  clearTimeout(overlapTimer);
  overlapError.value = '';
  overlapOk.value = false;
  if (!form.movie_id || !form.room_id || !form.show_date || !form.start_time) return;

  // Frontend guard: reject past datetime immediately
  if (isDatetimePast(form.show_date, form.start_time)) {
    overlapError.value = 'Thời gian bắt đầu suất chiếu không được ở trong quá khứ. Vui lòng chọn thời gian từ hiện tại trở đi.';
    return;
  }

  overlapTimer = setTimeout(async () => {
    checkingOverlap.value = true;
    try {
      const res = await axios.get(urls.checkOverlap, {
        params: {
          room_id:    form.room_id,
          show_date:  form.show_date,
          start_time: form.start_time,
          movie_id:   form.movie_id
        }
      });
      if (res.data.overlap) {
        overlapError.value = res.data.message;
      } else {
        overlapOk.value = true;
      }
    } catch (e) {
      overlapError.value = (e.response && e.response.data && e.response.data.message)
        ? e.response.data.message
        : 'Lỗi kiểm tra lịch chiếu.';
    } finally {
      checkingOverlap.value = false;
    }
  }, 400);
};

// Price suggestion
const triggerPriceSuggestion = () => {
  clearTimeout(priceTimer);
  if (!form.show_date || !form.start_time) return;
  priceTimer = setTimeout(async () => {
    try {
      const res = await axios.get(urls.suggestPrice, {
        params: { show_date: form.show_date, start_time: form.start_time }
      });
      surchargeAmt.value = res.data.suggested_price - 80000;
      priceReason.value = res.data.reason || '';
    } catch (e) { /* silent */ }
  }, 400);
};

const onMovieChange      = () => { fetchFormats(); triggerOverlapCheck(); };
const onDateOrTimeChange = () => { triggerOverlapCheck(); triggerPriceSuggestion(); };
const setPrice           = p => { standardPrice.value = p; };

// Submit Form
const submitForm = async () => {
  if (overlapError.value) { addToast('Không thể lưu do trùng lịch chiếu!', 'error'); return; }
  if (isDatetimePast(form.show_date, form.start_time)) {
    addToast('Thời gian bắt đầu suất chiếu không được ở trong quá khứ!', 'error');
    overlapError.value = 'Thời gian bắt đầu suất chiếu không được ở trong quá khứ. Vui lòng chọn thời gian từ hiện tại trở đi.';
    return;
  }
  Object.keys(fieldErrors).forEach(k => delete fieldErrors[k]);
  submitting.value = true;
  try {
    const res = await axios.post(urls.store, {
      movie_id:   form.movie_id,
      format_id:  form.format_id,
      cinema_id:  form.cinema_id,
      room_id:    form.room_id,
      show_date:  form.show_date,
      start_time: form.start_time,
      base_price: computedActualPrice.value,
      publish_at: isScheduled.value ? (publishAt.value || null) : null,
    });
    addToast(res.data.message || 'Tạo suất chiếu thành công!', 'success');
    setTimeout(() => { window.location.href = res.data.redirect || urls.redirect; }, 1500);
  } catch (e) {
    const d = e.response && e.response.data;
    if (d && d.errors) {
      Object.entries(d.errors).forEach(([k, v]) => {
        fieldErrors[k] = Array.isArray(v) ? v[0] : v;
      });
      const first = Object.values(d.errors).flat()[0];
      addToast(first, 'error');
    } else {
      addToast((d && d.message) ? d.message : 'Đã xảy ra lỗi hệ thống.', 'error');
    }
  } finally {
    submitting.value = false;
  }
};

onMounted(() => {
  fetchCinemas();
});
</script>

<style scoped>
.toast-fade-enter-active, .toast-fade-leave-active { transition: all .3s ease; }
.toast-fade-enter-from, .toast-fade-leave-to { opacity:0; transform:translateY(-12px); }
.mo-ban-fade-enter-active, .mo-ban-fade-leave-active { transition: opacity .25s ease, transform .25s ease; }
.mo-ban-fade-enter-from, .mo-ban-fade-leave-to { opacity:0; transform:translateY(-6px); }
</style>
