<template>
  <div class="space-y-6">
    <!-- Toast Notifications -->
    <div class="fixed top-4 right-4 z-50 space-y-2 pointer-events-none">
      <transition-group name="toast">
        <div v-for="toast in toasts" :key="toast.id"
             class="flex items-center gap-3 p-4 rounded-lg shadow-xl border text-sm max-w-md pointer-events-auto transition-all duration-300"
             :class="{
               'bg-emerald-50 border-emerald-200 text-emerald-800': toast.type === 'success',
               'bg-rose-50 border-rose-200 text-rose-800': toast.type === 'error',
               'bg-blue-50 border-blue-200 text-blue-800': toast.type === 'info'
             }">
          <span class="material-symbols-outlined shrink-0"
                :class="{
                  'text-emerald-500': toast.type === 'success',
                  'text-rose-500': toast.type === 'error',
                  'text-blue-500': toast.type === 'info'
                }">
            {{ toast.type === 'success' ? 'check_circle' : toast.type === 'error' ? 'error' : 'info' }}
          </span>
          <span class="font-medium flex-1">{{ toast.message }}</span>
          <button @click="removeToast(toast.id)" class="ml-2 text-slate-400 hover:text-slate-600 transition-colors shrink-0">
            <span class="material-symbols-outlined text-base">close</span>
          </button>
        </div>
      </transition-group>
    </div>

    <!-- Main Card -->
    <div class="bg-white border border-slate-200 shadow-sm p-8 rounded-none relative overflow-hidden">
      <!-- Accent Top Border -->
      <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500 to-indigo-600"></div>

      <form @submit.prevent="submitForm" class="space-y-6">

        <!-- Phim -->
        <div>
          <label for="movie_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Chon Phim</label>
          <select id="movie_id" v-model="form.movie_id" required @change="onMovieChange"
                  class="block w-full px-3 py-2.5 border border-slate-300 text-sm rounded-none focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 bg-white">
            <option value="">-- Chon phim --</option>
            <option v-for="movie in movies" :key="movie.id" :value="movie.id">
              {{ movie.title }} ({{ movie.duration }} phut)
            </option>
          </select>
          <p v-if="selectedMovie" class="mt-1.5 text-xs text-slate-500 flex items-center gap-1.5">
            <span class="material-symbols-outlined" style="font-size:13px">schedule</span>
            Thoi luong: <strong class="text-slate-700">{{ selectedMovie.duration }} phut</strong>
            <span class="mx-1">•</span>
            Gioi han tuoi:
            <span class="px-1.5 py-0.5 bg-slate-100 text-slate-700 font-semibold text-[10px] uppercase">
              {{ selectedMovie.age_limit || 'T18' }}
            </span>
          </p>
          <p v-if="errors.movie_id" class="mt-1 text-xs text-red-600 font-semibold">{{ errors.movie_id }}</p>
        </div>

        <!-- Phong Chieu -->
        <div>
          <label for="room_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Chon Phong Chieu</label>
          <select id="room_id" v-model="form.room_id" required @change="triggerOverlapCheck"
                  class="block w-full px-3 py-2.5 border border-slate-300 text-sm rounded-none focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 bg-white">
            <option value="">-- Chon phong chieu --</option>
            <option v-for="room in rooms" :key="room.id" :value="room.id">
              {{ room.room_name }} ({{ room.room_type }} - {{ room.capacity }} ghe)
            </option>
          </select>
          <p v-if="errors.room_id" class="mt-1 text-xs text-red-600 font-semibold">{{ errors.room_id }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Ngay Chieu -->
          <div>
            <label for="show_date" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Ngay Chieu</label>
            <input id="show_date" v-model="form.show_date" type="date" required
                   :min="today"
                   @change="onDateOrTimeChange"
                   class="block w-full px-3 py-2 border border-slate-300 text-sm rounded-none focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
            <p v-if="errors.show_date" class="mt-1 text-xs text-red-600 font-semibold">{{ errors.show_date }}</p>
          </div>

          <!-- Gio Bat Dau -->
          <div>
            <label for="start_time" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Gio Bat Dau</label>
            <input id="start_time" v-model="form.start_time" type="time" required
                   @change="onDateOrTimeChange"
                   class="block w-full px-3 py-2 border border-slate-300 text-sm rounded-none focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
            <p v-if="errors.start_time" class="mt-1 text-xs text-red-600 font-semibold">{{ errors.start_time }}</p>
          </div>
        </div>

        <!-- Thong tin thoi gian chieu -->
        <div class="p-4 bg-slate-50 border border-slate-200 space-y-2.5">
          <div class="flex justify-between items-center text-sm">
            <span class="text-slate-500 font-medium flex items-center gap-1.5">
              <span class="material-symbols-outlined" style="font-size:16px">update</span>
              Gio ket thuc du kien:
            </span>
            <span class="font-bold" :class="computedEndTime ? 'text-slate-800' : 'text-slate-400 italic font-normal'">
              {{ computedEndTime || '-- chua co thong tin --' }}
            </span>
          </div>

          <!-- Overlap Alert -->
          <transition name="fade">
            <div v-if="overlapError" class="flex gap-2 items-start text-rose-700 text-xs font-semibold p-3 bg-rose-50 border border-rose-200 rounded-none">
              <span class="material-symbols-outlined text-base shrink-0 mt-0.5">warning</span>
              <span>{{ overlapError }}</span>
            </div>
          </transition>

          <!-- Overlap OK -->
          <transition name="fade">
            <div v-if="overlapOk && !checkingOverlap" class="flex gap-2 items-center text-emerald-700 text-xs font-semibold p-2 bg-emerald-50 border border-emerald-200 rounded-none">
              <span class="material-symbols-outlined text-base shrink-0">check_circle</span>
              <span>Khung gio trong, co the xep lich.</span>
            </div>
          </transition>

          <!-- Checking spinner -->
          <div v-if="checkingOverlap" class="flex gap-2 items-center text-blue-600 text-xs font-semibold">
            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            <span>Dang kiem tra lich chieu...</span>
          </div>
        </div>

        <!-- Cấu hình giá vé (Strict Auto-calculation) -->
        <div class="space-y-4">
          <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 border-b border-slate-100 pb-2">Cấu Hình Giá Vé</h3>

          <!-- Badge hiển thị tên quy tắc giá -->
          <div v-if="surchargeLabel" class="flex items-center">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-50 text-amber-800 border border-amber-200 text-xs font-semibold rounded-none">
              <span class="material-symbols-outlined text-[15px]">sell</span>
              Quy tắc giá hoạt động: <strong class="text-amber-900">{{ surchargeLabel }}</strong>
            </span>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Ô 1: GIÁ TIÊU CHUẨN (VNĐ) -->
            <div>
              <label for="standard_price" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">
                Giá Tiêu Chuẩn (VNĐ)
              </label>
              <input
                id="standard_price"
                v-model.number="standardPrice"
                type="number"
                min="0"
                step="1000"
                required
                class="block w-full px-3 py-2 border border-slate-300 text-sm rounded-none focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600"
              >
              <div class="flex gap-2 mt-2">
                <button type="button" @click="standardPrice = 80000"
                        class="text-[11px] px-2.5 py-1 bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors rounded-none">80.000đ</button>
                <button type="button" @click="standardPrice = 100000"
                        class="text-[11px] px-2.5 py-1 bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors rounded-none">100.000đ</button>
                <button type="button" @click="standardPrice = 120000"
                        class="text-[11px] px-2.5 py-1 bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors rounded-none">120.000đ</button>
              </div>
              <p v-if="errors.base_price" class="mt-1 text-xs text-red-600 font-semibold">{{ errors.base_price }}</p>
            </div>

            <!-- Ô 2: PHỤ THU NGÀY/GIỜ -->
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1 flex items-center gap-1">
                <span class="material-symbols-outlined" style="font-size:13px">auto_awesome</span>
                Phụ Thu Ngày/Giờ
              </label>
              <input
                type="text"
                :value="surchargeText"
                disabled
                class="block w-full px-3 py-2 border border-slate-200 text-sm rounded-none bg-slate-50 text-slate-500 font-semibold cursor-not-allowed"
              >
              <p class="mt-1.5 text-[10px] text-slate-400">Tự động tính theo ngày lễ / khung giờ</p>
            </div>

            <!-- Ô 3: GIÁ VÉ THỰC TẾ (LƯU DB) -->
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1 flex items-center gap-1">
                <span class="material-symbols-outlined" style="font-size:13px">payments</span>
                Giá Vé Thực Tế (Lưu DB)
              </label>
              <input
                type="text"
                :value="actualPriceFormatted"
                disabled
                class="block w-full px-3 py-2 border border-blue-200 text-sm rounded-none bg-blue-50 text-blue-800 font-bold cursor-not-allowed"
              >
              <p class="mt-1.5 text-[10px] text-slate-400">= Giá tiêu chuẩn + Phụ thu</p>
            </div>
          </div>
        </div>

        <!-- Submit -->
        <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
          <a :href="cancelUrl"
             class="px-5 py-2.5 border border-slate-300 text-slate-700 text-sm font-semibold rounded-none hover:bg-slate-50 transition-colors">
            Huy bo
          </a>
          <button type="submit"
                  :disabled="submitting || !!overlapError"
                  class="px-5 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-none hover:bg-blue-700 disabled:bg-slate-300 disabled:cursor-not-allowed transition-colors flex items-center gap-2">
            <svg v-if="submitting" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            <span>{{ submitting ? 'Dang xu ly...' : 'Len Lich Suat Chieu' }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import axios from 'axios';

// Props tu Blade — tat ca URL duoc truyen qua props, khong hardcode
const props = defineProps({
  movies: { type: Array, required: true },
  rooms:  { type: Array, required: true },
  cancelUrl:        { type: String, required: true },
  checkOverlapUrl:  { type: String, required: true },
  suggestPriceUrl:  { type: String, required: true },
  storeUrl:         { type: String, required: true },
  redirectUrl:      { type: String, required: true },
});

// Thiet lap axios CSRF token mac dinh
onMounted(() => {
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
  }
});

// Ngay hom nay (dung cho min date)
const today = new Date().toISOString().split('T')[0];

// Form state
const form = reactive({
  movie_id:   '',
  room_id:    '',
  show_date:  today,
  start_time: '',
});

// === State Cấu hình giá vé (Strict Auto-calculation) ===
// Ô 1: Số tiền tiêu chuẩn do người dùng nhập (Mặc định: 80000)
const standardPrice   = ref(80000);
// Ô 2: Số tiền phụ thu lấy từ API khi chọn ngày/giờ (Mặc định: 0)
const surchargeAmount = ref(0);
// Tên của quy tắc phụ thu lấy từ API
const surchargeLabel  = ref('');

const errors          = reactive({});
const checkingOverlap = ref(false);
const overlapError    = ref('');
const overlapOk       = ref(false);
const submitting      = ref(false);
const toasts          = ref([]);
let   overlapTimer    = null;
let   priceTimer      = null;

// Phim dang chon
const selectedMovie = computed(() =>
  props.movies.find(m => m.id === Number(form.movie_id)) || null
);

// Tinh gio ket thuc tu dong
const computedEndTime = computed(() => {
  if (!selectedMovie.value || !form.start_time) return '';
  const [h, m]     = form.start_time.split(':').map(Number);
  const totalMins  = h * 60 + m + selectedMovie.value.duration;
  const eh = Math.floor(totalMins / 60) % 24;
  const em = totalMins % 60;
  return `${String(eh).padStart(2,'0')}:${String(em).padStart(2,'0')}`;
});

// === Computed Properties Giá Vé ===
// Giá thực tế = Giá tiêu chuẩn + Phụ thu
const actualPrice = computed(() => standardPrice.value + surchargeAmount.value);

// Format giá thực tế sang VNĐ để hiển thị ra UI ở Ô 3
const actualPriceFormatted = computed(() => {
  return actualPrice.value.toLocaleString('vi-VN') + ' đ';
});

// Text hiển thị phụ thu ở Ô 2
const surchargeText = computed(() => {
  if (surchargeAmount.value === 0) return 'Không có phụ thu';
  const sign   = surchargeAmount.value >= 0 ? '+' : '';
  const amount = Math.abs(surchargeAmount.value).toLocaleString('vi-VN');
  return `${sign} ${amount} đ`;
});

// Toasts
const addToast = (message, type = 'success') => {
  const id = Date.now();
  toasts.value.push({ id, message, type });
  setTimeout(() => removeToast(id), 5000);
};
const removeToast = (id) => {
  toasts.value = toasts.value.filter(t => t.id !== id);
};

// Khi doi phim
const onMovieChange = () => {
  triggerOverlapCheck();
};

// Khi doi ngay / gio
const onDateOrTimeChange = () => {
  triggerOverlapCheck();
  triggerPriceSuggestion();
};

// Debounce kiem tra trung lich 400ms
const triggerOverlapCheck = () => {
  clearTimeout(overlapTimer);
  if (!form.movie_id || !form.room_id || !form.show_date || !form.start_time) {
    overlapError.value = '';
    overlapOk.value = false;
    return;
  }
  overlapTimer = setTimeout(doCheckOverlap, 400);
};

const doCheckOverlap = async () => {
  checkingOverlap.value = true;
  overlapError.value    = '';
  overlapOk.value       = false;
  try {
    const { data } = await axios.get(props.checkOverlapUrl, {
      params: {
        room_id:    form.room_id,
        show_date:  form.show_date,
        start_time: form.start_time,
        movie_id:   form.movie_id,
      },
    });
    if (data.overlap) {
      overlapError.value = data.message;
    } else {
      overlapOk.value = true;
    }
  } catch (e) {
    const msg = e.response?.data?.message;
    overlapError.value = msg || 'Khong the kiem tra lich chieu. Vui long thu lai.';
  } finally {
    checkingOverlap.value = false;
  }
};

// Debounce goi y gia 400ms
const triggerPriceSuggestion = () => {
  clearTimeout(priceTimer);
  if (!form.show_date || !form.start_time) return;
  priceTimer = setTimeout(doSuggestPrice, 400);
};

const doSuggestPrice = async () => {
  try {
    const { data } = await axios.get(props.suggestPriceUrl, {
      params: { show_date: form.show_date, start_time: form.start_time },
    });
    // API tra ve suggested_price da bao gom phu thu
    // Tinh nguoc: surcharge = suggested_price - gia goc mac dinh (80000)
    const BASE_STANDARD    = 80000;
    const suggested        = data.suggested_price ?? BASE_STANDARD;
    surchargeAmount.value  = suggested - BASE_STANDARD;
    surchargeLabel.value   = (data.reason && data.reason !== 'Giá cơ bản ngày thường')
      ? data.reason
      : '';
  } catch (e) {
    // Khong anh huong nghiep vu — reset phu thu ve 0
    surchargeAmount.value = 0;
    surchargeLabel.value  = '';
  }
};

// Submit
const submitForm = async () => {
  if (overlapError.value) {
    addToast('Khong the luu do trung lich chieu!', 'error');
    return;
  }

  // Reset errors
  Object.keys(errors).forEach(k => delete errors[k]);
  submitting.value = true;

  try {
    const { data } = await axios.post(props.storeUrl, {
      movie_id:   form.movie_id,
      room_id:    form.room_id,
      show_date:  form.show_date,
      start_time: form.start_time,
      base_price: actualPrice.value, // Gui gia thuc te (tieu chuan + phu thu)
    });

    addToast(data.message || 'Tao suat chieu thanh cong!', 'success');
    setTimeout(() => {
      window.location.href = data.redirect || props.redirectUrl;
    }, 1500);
  } catch (e) {
    const data = e.response?.data;
    if (data?.errors) {
      Object.assign(errors, Object.fromEntries(
        Object.entries(data.errors).map(([k, v]) => [k, Array.isArray(v) ? v[0] : v])
      ));
      const firstMsg = Object.values(data.errors).flat()[0];
      addToast(firstMsg, 'error');
    } else {
      addToast(data?.message || 'Da xay ra loi. Vui long thu lai.', 'error');
    }
  } finally {
    submitting.value = false;
  }
};
</script>

<style scoped>
.toast-enter-active, .toast-leave-active { transition: all 0.3s ease; }
.toast-enter-from, .toast-leave-to { opacity: 0; transform: translateY(-16px) scale(0.95); }

.fade-enter-active, .fade-leave-active { transition: opacity 0.25s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
