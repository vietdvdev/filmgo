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
          <label for="movie_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Chọn Phim</label>
          <select id="movie_id" v-model="form.movie_id" required @change="onMovieChange"
                  class="block w-full px-3 py-2.5 border border-slate-300 text-sm rounded-none focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 bg-white">
            <option value="">-- Chọn phim --</option>
            <option v-for="movie in movies" :key="movie.id" :value="movie.id">
              {{ movie.title }} ({{ movie.duration }} phút)
            </option>
          </select>
          <p v-if="selectedMovie" class="mt-1.5 text-xs text-slate-500 flex items-center gap-1.5">
            <span class="material-symbols-outlined" style="font-size:13px">schedule</span>
            Thời lượng: <strong class="text-slate-700">{{ selectedMovie.duration }} phút</strong>
            <span class="mx-1">•</span>
            Giới hạn tuổi:
            <span class="px-1.5 py-0.5 bg-slate-100 text-slate-700 font-semibold text-[10px] uppercase">
              {{ selectedMovie.age_limit || 'T18' }}
            </span>
          </p>
          <p v-if="errors.movie_id" class="mt-1 text-xs text-red-600 font-semibold">{{ errors.movie_id }}</p>
        </div>

        <!-- Phòng Chiếu -->
        <div>
          <label for="room_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Chọn Phòng Chiếu</label>
          <select id="room_id" v-model="form.room_id" required @change="triggerOverlapCheck"
                  class="block w-full px-3 py-2.5 border border-slate-300 text-sm rounded-none focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 bg-white">
            <option value="">-- Chọn phòng chiếu --</option>
            <option v-for="room in rooms" :key="room.id" :value="room.id">
              {{ room.room_name }} ({{ room.room_type }} – {{ room.capacity }} ghế)
            </option>
          </select>
          <p v-if="errors.room_id" class="mt-1 text-xs text-red-600 font-semibold">{{ errors.room_id }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Ngày Chiếu -->
          <div>
            <label for="show_date" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Ngày Chiếu</label>
            <input id="show_date" v-model="form.show_date" type="date" required
                   :min="today"
                   @change="onDateOrTimeChange"
                   class="block w-full px-3 py-2 border border-slate-300 text-sm rounded-none focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
            <p v-if="errors.show_date" class="mt-1 text-xs text-red-600 font-semibold">{{ errors.show_date }}</p>
          </div>

          <!-- Giờ Bắt Đầu -->
          <div>
            <label for="start_time" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Giờ Bắt Đầu</label>
            <input id="start_time" v-model="form.start_time" type="time" required
                   @change="onDateOrTimeChange"
                   class="block w-full px-3 py-2 border border-slate-300 text-sm rounded-none focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
            <p v-if="errors.start_time" class="mt-1 text-xs text-red-600 font-semibold">{{ errors.start_time }}</p>
          </div>
        </div>

        <!-- Thông tin thời gian chiếu -->
        <div class="p-4 bg-slate-50 border border-slate-200 space-y-2.5">
          <div class="flex justify-between items-center text-sm">
            <span class="text-slate-500 font-medium flex items-center gap-1.5">
              <span class="material-symbols-outlined" style="font-size:16px">update</span>
              Giờ kết thúc dự kiến:
            </span>
            <span class="font-bold" :class="computedEndTime ? 'text-slate-800' : 'text-slate-400 italic font-normal'">
              {{ computedEndTime || '— chưa có thông tin —' }}
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
              <span>Khung giờ trống, có thể xếp lịch.</span>
            </div>
          </transition>

          <!-- Checking spinner -->
          <div v-if="checkingOverlap" class="flex gap-2 items-center text-blue-600 text-xs font-semibold">
            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            <span>Đang kiểm tra lịch chiếu...</span>
          </div>
        </div>

        <!-- Giá Vé -->
        <div>
          <div class="flex justify-between items-center mb-1">
            <label for="base_price" class="block text-xs font-bold uppercase tracking-wider text-slate-700">Giá Vé Cơ Bản (VNĐ)</label>
            <transition name="fade">
              <span v-if="priceSuggestionReason"
                    class="text-[10px] font-bold bg-amber-100 text-amber-800 px-2 py-0.5 rounded-full flex items-center gap-1">
                <span class="material-symbols-outlined" style="font-size:10px">auto_awesome</span>
                {{ priceSuggestionReason }}
              </span>
            </transition>
          </div>
          <input id="base_price" v-model.number="form.base_price" type="number" required min="0" step="1000"
                 class="block w-full px-3 py-2 border border-slate-300 text-sm rounded-none focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
          <div class="flex gap-2 mt-2">
            <button type="button" @click="form.base_price = 80000"
                    class="text-[11px] px-2.5 py-1 bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors rounded-none">
              80.000đ
            </button>
            <button type="button" @click="form.base_price = 100000"
                    class="text-[11px] px-2.5 py-1 bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors rounded-none">
              100.000đ
            </button>
            <button type="button" @click="form.base_price = 120000"
                    class="text-[11px] px-2.5 py-1 bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors rounded-none">
              120.000đ
            </button>
          </div>
          <p v-if="errors.base_price" class="mt-1 text-xs text-red-600 font-semibold">{{ errors.base_price }}</p>
        </div>

        <!-- Submit -->
        <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
          <a :href="cancelUrl"
             class="px-5 py-2.5 border border-slate-300 text-slate-700 text-sm font-semibold rounded-none hover:bg-slate-50 transition-colors">
            Hủy bỏ
          </a>
          <button type="submit"
                  :disabled="submitting || !!overlapError"
                  class="px-5 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-none hover:bg-blue-700 disabled:bg-slate-300 disabled:cursor-not-allowed transition-colors flex items-center gap-2">
            <svg v-if="submitting" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            <span>{{ submitting ? 'Đang xử lý...' : 'Lên Lịch Suất Chiếu' }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import axios from 'axios';

// Props từ Blade — tất cả URL được truyền qua props, không hardcode
const props = defineProps({
  movies: { type: Array, required: true },
  rooms:  { type: Array, required: true },
  cancelUrl:        { type: String, required: true },
  checkOverlapUrl:  { type: String, required: true },
  suggestPriceUrl:  { type: String, required: true },
  storeUrl:         { type: String, required: true },
  redirectUrl:      { type: String, required: true },
});

// Thiết lập axios CSRF token mặc định
onMounted(() => {
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
  }
});

// Ngày hôm nay (dùng cho min date)
const today = new Date().toISOString().split('T')[0];

// Form state
const form = reactive({
  movie_id:   '',
  room_id:    '',
  show_date:  today,
  start_time: '',
  base_price: 80000,
});

const errors          = reactive({});
const checkingOverlap = ref(false);
const overlapError    = ref('');
const overlapOk       = ref(false);
const priceSuggestionReason = ref('');
const submitting      = ref(false);
const toasts          = ref([]);
let   overlapTimer    = null;
let   priceTimer      = null;

// Phim đang chọn
const selectedMovie = computed(() =>
  props.movies.find(m => m.id === Number(form.movie_id)) || null
);

// Tính giờ kết thúc tự động
const computedEndTime = computed(() => {
  if (!selectedMovie.value || !form.start_time) return '';
  const [h, m]     = form.start_time.split(':').map(Number);
  const totalMins  = h * 60 + m + selectedMovie.value.duration;
  const eh = Math.floor(totalMins / 60) % 24;
  const em = totalMins % 60;
  return `${String(eh).padStart(2,'0')}:${String(em).padStart(2,'0')}`;
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

// Khi đổi phim
const onMovieChange = () => {
  triggerOverlapCheck();
};

// Khi đổi ngày / giờ
const onDateOrTimeChange = () => {
  triggerOverlapCheck();
  triggerPriceSuggestion();
};

// Debounce kiểm tra trùng lịch 400ms
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
    overlapError.value = msg || 'Không thể kiểm tra lịch chiếu. Vui lòng thử lại.';
  } finally {
    checkingOverlap.value = false;
  }
};

// Debounce gợi ý giá 400ms
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
    form.base_price = data.suggested_price;
    priceSuggestionReason.value = data.reason || '';
  } catch (e) {
    // Không cần thông báo lỗi gợi ý giá — không ảnh hưởng nghiệp vụ
  }
};

// Submit
const submitForm = async () => {
  if (overlapError.value) {
    addToast('Không thể lưu do trùng lịch chiếu!', 'error');
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
      base_price: form.base_price,
    });

    addToast(data.message || 'Tạo suất chiếu thành công!', 'success');
    setTimeout(() => {
      window.location.href = data.redirect || props.redirectUrl;
    }, 1500);
  } catch (e) {
    const data = e.response?.data;
    if (data?.errors) {
      Object.assign(errors, Object.fromEntries(
        Object.entries(data.errors).map(([k, v]) => [k, Array.isArray(v) ? v[0] : v])
      ));
      // Show first error as toast
      const firstMsg = Object.values(data.errors).flat()[0];
      addToast(firstMsg, 'error');
    } else {
      addToast(data?.message || 'Đã xảy ra lỗi. Vui lòng thử lại.', 'error');
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
