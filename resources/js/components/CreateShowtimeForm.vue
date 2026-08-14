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

        <!-- 1. Rạp chiếu -->
        <div>
          <label for="cinema_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
            1. Chọn Rạp Chiếu
          </label>
          <select id="cinema_id" v-model="form.cinema_id" required @change="onCinemaChange"
            :disabled="loadingCinemas"
            class="block w-full px-3 py-2.5 border border-slate-300 text-sm focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 bg-white disabled:bg-slate-100 disabled:text-slate-400">
            <option value="">-- {{ loadingCinemas ? 'Đang tải danh sách rạp...' : 'Chọn rạp chiếu' }} --</option>
            <option v-for="c in cinemas" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
          <p v-if="fieldErrors.cinema_id" class="mt-1 text-xs text-red-600 font-semibold">{{ fieldErrors.cinema_id }}</p>
        </div>

        <!-- 2. Phim -->
        <div>
          <label for="movie_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
            2. Chọn Phim Chiếu
          </label>
          <select id="movie_id" v-model="form.movie_id" required @change="onMovieChange"
            class="block w-full px-3 py-2.5 border border-slate-300 text-sm focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 bg-white">
            <option value="">-- Chọn phim chiếu --</option>
            <option v-for="m in movies" :key="m.id" :value="m.id">
              {{ m.title }} ({{ m.duration }} phút)
            </option>
          </select>
          <p v-if="selectedMovie" class="mt-1.5 text-xs text-slate-500">
            Thời lượng: <strong>{{ selectedMovie.duration }} phút</strong>
            &nbsp;•&nbsp;
            Giới hạn tuổi: <strong>{{ selectedMovie.age_limit || 'T18' }}</strong>
          </p>
          <div v-if="selectedMovie && selectedMovie.formats && selectedMovie.formats.length" class="mt-2 flex flex-wrap gap-1.5">
            <span
              v-for="fmt in selectedMovie.formats"
              :key="fmt.id"
              class="inline-flex items-center gap-1 px-2.5 py-1 rounded text-[11px] font-bold border"
              :class="formatBadgeClass(fmt.name)">
              {{ fmt.name }}
              <span v-if="fmt.surcharge_price > 0" class="font-normal opacity-75">+{{ Number(fmt.surcharge_price).toLocaleString() }}₫</span>
            </span>
          </div>
          <p v-if="fieldErrors.movie_id" class="mt-1 text-xs text-red-600 font-semibold">{{ fieldErrors.movie_id }}</p>
        </div>

        <!-- 3. Phòng chiếu (chỉ hiện sau khi chọn phim) -->
        <div v-if="form.movie_id">
          <label for="room_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
            3. Chọn Phòng Chiếu
          </label>
          <div v-if="loadingRooms" class="flex items-center gap-2 text-blue-600 text-xs font-semibold py-2">
            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            Đang tải danh sách phòng chiếu phù hợp...
          </div>
          <template v-else>
            <div v-if="rooms.length === 0"
              class="flex items-center gap-2 p-3 bg-amber-50 border border-amber-200 text-amber-800 text-xs font-semibold">
              <span class="material-symbols-outlined text-base">warning</span>
              Rạp này không có phòng chiếu nào phù hợp với phim đã chọn.
            </div>
            <select v-else id="room_id" v-model="form.room_id" required @change="onRoomChange"
              class="block w-full px-3 py-2.5 border border-slate-300 text-sm focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 bg-white">
              <option value="">-- Chọn phòng chiếu --</option>
              <option v-for="r in rooms" :key="r.id" :value="r.id">
                {{ r.room_name }} ({{ r.room_type }} – {{ r.capacity }} ghế)
              </option>
            </select>
          </template>
          <p v-if="fieldErrors.room_id" class="mt-1 text-xs text-red-600 font-semibold">{{ fieldErrors.room_id }}</p>
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

        <!-- Overlap -->
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

        <!-- Cấu hình giá vé -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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

          <div class="bg-amber-50/50 border border-amber-200/70 p-4 flex flex-col justify-between">
            <div>
              <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">2. PHỤ THU</h4>
              <div class="text-lg font-bold text-amber-800">
                + {{ (surchargeAmt + formatSurcharge).toLocaleString() }} ₫
              </div>
              <div class="mt-1 space-y-0.5">
                <p v-if="priceReason" class="text-[11px] text-amber-700 italic leading-tight">
                  Ngày/giờ: {{ surchargeAmt.toLocaleString() }}đ ({{ priceReason }})
                </p>
                <p v-else class="text-[11px] text-slate-400 italic">Ngày/giờ: 0đ</p>
                <p v-if="formatSurcharge > 0" class="text-[11px] text-indigo-700 italic leading-tight">
                  Định dạng: +{{ formatSurcharge.toLocaleString() }}đ
                </p>
              </div>
            </div>
          </div>

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

const { movies, csrfToken, urls } = window.__SHOWTIME_DATA__ || { movies: [], csrfToken: '', urls: {} };

axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
axios.defaults.headers.common['Accept']       = 'application/json';

const today = new Date().toISOString().split('T')[0];

const isDatetimePast = (showDate, startTime) => {
  if (!showDate || !startTime) return false;
  return new Date(`${showDate}T${startTime}:00`) < new Date();
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
const autoFormat      = ref(null);
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

let overlapTimer = null;
let priceTimer   = null;

const selectedMovie = computed(() => movies.find(m => m.id == form.movie_id) || null);

const formatBadgeClass = (name) => {
  const map = {
    '2D':   'bg-blue-50 text-blue-700 border-blue-300',
    '3D':   'bg-purple-50 text-purple-700 border-purple-300',
    'IMAX': 'bg-amber-50 text-amber-700 border-amber-300',
    '4DX':  'bg-emerald-50 text-emerald-700 border-emerald-300',
  };
  return map[name] || 'bg-slate-100 text-slate-700 border-slate-300';
};

const computedEndTime = computed(() => {
  if (!selectedMovie.value || !form.start_time) return '';
  const [h, m] = form.start_time.split(':').map(Number);
  const total  = h * 60 + m + selectedMovie.value.duration;
  const eh     = Math.floor(total / 60) % 24;
  const em     = total % 60;
  return String(eh).padStart(2, '0') + ':' + String(em).padStart(2, '0');
});

const addToast = (message, type) => {
  const id = Date.now();
  toasts.value.push({ id, message, type: type || 'success' });
  setTimeout(() => removeToast(id), 5000);
};
const removeToast = id => { toasts.value = toasts.value.filter(t => t.id !== id); };

const BASE_PRICE    = 80000;
const standardPrice = ref(BASE_PRICE);
const surchargeAmt  = ref(0);

const formatSurcharge = computed(() =>
  autoFormat.value ? Number(autoFormat.value.surcharge_price || 0) : 0
);

const computedActualPrice = computed(() =>
  Number(standardPrice.value || 0) + Number(surchargeAmt.value || 0) + formatSurcharge.value
);

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

const onCinemaChange = () => {
  form.movie_id      = '';
  form.room_id       = '';
  form.format_id     = '';
  autoFormat.value   = null;
  rooms.value        = [];
  formats.value      = [];
  overlapError.value = '';
  overlapOk.value    = false;
};

const onMovieChange = async () => {
  form.room_id       = '';
  form.format_id     = '';
  autoFormat.value   = null;
  rooms.value        = [];
  formats.value      = [];
  overlapError.value = '';
  overlapOk.value    = false;

  if (!form.movie_id || !form.cinema_id) return;

  loadingRooms.value = true;
  try {
    const url = urls.roomsByMovie.replace(':movie_id', form.movie_id);
    const res = await axios.get(url);
    rooms.value = (res.data.data || []).filter(r => r.cinema_id == form.cinema_id);
  } catch (e) {
    addToast('Không thể tải danh sách phòng chiếu phù hợp.', 'error');
  } finally {
    loadingRooms.value = false;
  }
};

const onRoomChange = async () => {
  autoFormat.value   = null;
  form.format_id     = '';
  formats.value      = [];
  overlapError.value = '';
  overlapOk.value    = false;

  if (!form.room_id || !form.movie_id) return;

  loadingFormats.value = true;
  try {
    const res = await axios.get(`/api/rooms/${form.room_id}/movies/${form.movie_id}/formats`);
    formats.value = res.data.data || res.data || [];
    if (formats.value.length > 0) {
      autoFormat.value = formats.value[0];
      form.format_id   = formats.value[0].id;
    }
  } catch (e) {
    addToast('Không thể tải định dạng chiếu.', 'error');
  } finally {
    loadingFormats.value = false;
  }

  triggerOverlapCheck();
  triggerPriceSuggestion();
};

const triggerOverlapCheck = () => {
  clearTimeout(overlapTimer);
  overlapError.value = '';
  overlapOk.value    = false;
  if (!form.movie_id || !form.room_id || !form.show_date || !form.start_time) return;

  if (isDatetimePast(form.show_date, form.start_time)) {
    overlapError.value = 'Thời gian bắt đầu suất chiếu không được ở trong quá khứ. Vui lòng chọn thời gian từ hiện tại trở đi.';
    return;
  }

  overlapTimer = setTimeout(async () => {
    checkingOverlap.value = true;
    try {
      const res = await axios.get(urls.checkOverlap, {
        params: { room_id: form.room_id, show_date: form.show_date, start_time: form.start_time, movie_id: form.movie_id }
      });
      if (res.data.overlap) {
        overlapError.value = res.data.message;
      } else {
        overlapOk.value = true;
      }
    } catch (e) {
      overlapError.value = (e.response?.data?.message) ? e.response.data.message : 'Lỗi kiểm tra lịch chiếu.';
    } finally {
      checkingOverlap.value = false;
    }
  }, 400);
};

const triggerPriceSuggestion = () => {
  clearTimeout(priceTimer);
  if (!form.show_date || !form.start_time) return;
  priceTimer = setTimeout(async () => {
    try {
      const res = await axios.get(urls.suggestPrice, {
        params: { show_date: form.show_date, start_time: form.start_time }
      });
      surchargeAmt.value = Number(res.data.suggested_price || BASE_PRICE) - BASE_PRICE;
      priceReason.value  = res.data.reason || '';
    } catch (e) { /* silent */ }
  }, 400);
};

const onDateOrTimeChange = () => { triggerOverlapCheck(); triggerPriceSuggestion(); };
const setPrice = p => { standardPrice.value = p; };

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
      publish_at: null,
    });
    addToast(res.data.message || 'Tạo suất chiếu thành công!', 'success');
    setTimeout(() => { window.location.href = res.data.redirect || urls.redirect; }, 1500);
  } catch (e) {
    const d = e.response?.data;
    if (d?.errors) {
      Object.entries(d.errors).forEach(([k, v]) => { fieldErrors[k] = Array.isArray(v) ? v[0] : v; });
      addToast(Object.values(d.errors).flat()[0], 'error');
    } else {
      addToast(d?.message || 'Đã xảy ra lỗi hệ thống.', 'error');
    }
  } finally {
    submitting.value = false;
  }
};

onMounted(() => { fetchCinemas(); });
</script>

<style scoped>
.toast-fade-enter-active, .toast-fade-leave-active { transition: all .3s ease; }
.toast-fade-enter-from, .toast-fade-leave-to { opacity:0; transform:translateY(-12px); }
</style>
