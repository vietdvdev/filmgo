/**
 * FilmGo — Auto Generate Showtime Component & App
 * Vue 3 CDN (global build)
 */
(function () {
    const { createApp, ref, reactive, onMounted } = Vue;
    const { movies, csrfToken } = window.__SHOWTIME_DATA__;

    // Axios defaults
    axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
    axios.defaults.headers.common['Accept']       = 'application/json';

    const todayDate = new Date().toISOString().split('T')[0];

    // Define Component Options
    const AutoGenerateShowtimeForm = {
        props: {
            myCinemasUrl: {
                type: String,
                default: '/manager/api/my-cinemas'
            },
            roomsUrlPattern: {
                type: String,
                default: '/manager/api/cinemas/:cinema_id/rooms'
            },
            autoGenerateUrl: {
                type: String,
                default: '/manager/showtimes/api/auto-generate'
            },
            cancelUrl: {
                type: String,
                default: '/manager/showtimes'
            }
        },
        setup(props) {
            const payload = reactive({
                movie_id: '',
                room_id: '',
                show_date: todayDate,
                shift_start: '08:00',
                shift_end: '23:00',
                cleaning_time: 20,
                standard_price: 80000
            });

            const isLoading = ref(false);
            const errors = ref({});
            const globalSuccessMessage = ref('');
            const globalErrorMessage = ref('');

            const moviesList = ref(movies);
            const roomsList = ref([]);

            onMounted(() => {
                fetchAssignedCinemaRooms();
            });

            const fetchAssignedCinemaRooms = async () => {
                try {
                    const cinemaRes = await axios.get(props.myCinemasUrl);
                    if (cinemaRes.data && cinemaRes.data.length > 0) {
                        const cinemaId = cinemaRes.data[0].id;
                        const url = props.roomsUrlPattern.replace(':cinema_id', cinemaId);
                        const roomRes = await axios.get(url);
                        roomsList.value = roomRes.data;
                    }
                } catch (e) {
                    globalErrorMessage.value = 'Không thể tải cấu hình phòng chiếu của rạp được phân công.';
                }
            };

            const setPrice = (amount) => {
                payload.standard_price = amount;
            };

            const submitAutoGenerate = async () => {
                isLoading.value = true;
                errors.value = {};
                globalSuccessMessage.value = '';
                globalErrorMessage.value = '';

                try {
                    const res = await axios.post(props.autoGenerateUrl, payload);
                    
                    if (res.data && res.data.success) {
                        globalSuccessMessage.value = `Đã tạo thành công ${res.data.total_generated} suất chiếu mới!`;
                        // Reset form
                        payload.movie_id = '';
                        payload.room_id = '';
                        payload.show_date = todayDate;
                        payload.shift_start = '08:00';
                        payload.shift_end = '23:00';
                        payload.cleaning_time = 20;
                        payload.standard_price = 80000;
                    }
                } catch (error) {
                    const status = error.response ? error.response.status : null;
                    const data = error.response ? error.response.data : null;

                    if (status === 422 && data && data.errors) {
                        Object.entries(data.errors).forEach(([field, msg]) => {
                            errors.value[field] = Array.isArray(msg) ? msg[0] : msg;
                        });
                        globalErrorMessage.value = 'Vui lòng kiểm tra lại thông tin các trường lỗi.';
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

            return {
                payload, isLoading, errors,
                globalSuccessMessage, globalErrorMessage,
                moviesList, roomsList, todayDate,
                setPrice, submitAutoGenerate, cancelAutoGenerate
            };
        },
        template: `
  <div class="bg-white border border-slate-200 shadow-md p-8 rounded-none relative overflow-hidden">
    <!-- Top Accent Bar -->
    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-purple-500 to-indigo-600"></div>

    <!-- Alert / Toast Messages -->
    <div v-if="globalSuccessMessage" class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-none flex items-start gap-3">
      <span class="material-symbols-outlined text-emerald-500 shrink-0">check_circle</span>
      <div class="flex-1 text-sm font-medium">{{ globalSuccessMessage }}</div>
      <button @click="globalSuccessMessage = ''" class="text-emerald-400 hover:text-emerald-600">
        <span class="material-symbols-outlined text-base">close</span>
      </button>
    </div>

    <div v-if="globalErrorMessage" class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-none flex items-start gap-3">
      <span class="material-symbols-outlined text-rose-500 shrink-0">error</span>
      <div class="flex-1 text-sm font-medium">{{ globalErrorMessage }}</div>
      <button @click="globalErrorMessage = ''" class="text-rose-400 hover:text-rose-600">
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

    <form @submit.prevent="submitAutoGenerate" class="space-y-6">
      
      <!-- Block 1 (Base Info - Grid 3 cols) -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Chọn Phim -->
        <div>
          <label for="movie_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
            Chọn Phim
          </label>
          <select
            id="movie_id"
            v-model="payload.movie_id"
            required
            class="block w-full px-3 py-2.5 border border-slate-300 text-sm focus:outline-none focus:border-purple-600 focus:ring-1 focus:ring-purple-600 bg-white rounded-none disabled:bg-slate-100 disabled:text-slate-400"
          >
            <option value="">-- Chọn phim --</option>
            <option v-for="movie in moviesList" :key="movie.id" :value="movie.id">
              {{ movie.title }} ({{ movie.duration }} phút)
            </option>
          </select>
          <p v-if="errors.movie_id" class="mt-1 text-xs text-rose-600 font-semibold">{{ errors.movie_id }}</p>
        </div>

        <!-- Chọn Phòng Chiếu -->
        <div>
          <label for="room_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
            Chọn Phòng Chiếu
          </label>
          <select
            id="room_id"
            v-model="payload.room_id"
            required
            class="block w-full px-3 py-2.5 border border-slate-300 text-sm focus:outline-none focus:border-purple-600 focus:ring-1 focus:ring-purple-600 bg-white rounded-none disabled:bg-slate-100 disabled:text-slate-400"
          >
            <option value="">-- Chọn phòng --</option>
            <option v-for="room in roomsList" :key="room.id" :value="room.id">
              {{ room.room_name }} ({{ room.room_type }} – {{ room.capacity }} ghế)
            </option>
          </select>
          <p v-if="errors.room_id" class="mt-1 text-xs text-rose-600 font-semibold">{{ errors.room_id }}</p>
        </div>

        <!-- Ngày Chiếu -->
        <div>
          <label for="show_date" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
            Ngày Chiếu
          </label>
          <input
            id="show_date"
            v-model="payload.show_date"
            type="date"
            required
            :min="todayDate"
            class="block w-full px-3 py-2 border border-slate-300 text-sm focus:outline-none focus:border-purple-600 focus:ring-1 focus:ring-purple-600 rounded-none bg-white"
          />
          <p v-if="errors.show_date" class="mt-1 text-xs text-rose-600 font-semibold">{{ errors.show_date }}</p>
        </div>
      </div>

      <!-- Block 2 (Shift Configuration - Grid 3 cols, wrapped in bg-gray-50 p-4) -->
      <div class="bg-gray-50 border border-slate-200/80 p-5 space-y-4">
        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 border-b border-slate-200 pb-2">
          Cấu hình ca làm việc
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
              required
              class="block w-full px-3 py-2 border border-slate-300 text-sm focus:outline-none focus:border-purple-600 focus:ring-1 focus:ring-purple-600 rounded-none bg-white"
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
              required
              class="block w-full px-3 py-2 border border-slate-300 text-sm focus:outline-none focus:border-purple-600 focus:ring-1 focus:ring-purple-600 rounded-none bg-white"
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
              required
              min="0"
              class="block w-full px-3 py-2 border border-slate-300 text-sm focus:outline-none focus:border-purple-600 focus:ring-1 focus:ring-purple-600 rounded-none bg-white"
            />
            <p v-if="errors.cleaning_time" class="mt-1 text-xs text-rose-600 font-semibold">{{ errors.cleaning_time }}</p>
          </div>
        </div>
      </div>

      <!-- Block 3 (Financial Config - Grid 1 col) -->
      <div>
        <label for="standard_price" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
          Giá Vé Tiêu Chuẩn (VNĐ)
        </label>
        <input
          id="standard_price"
          v-model.number="payload.standard_price"
          type="number"
          required
          min="0"
          step="1000"
          class="block w-full px-3 py-2 border border-slate-300 text-sm focus:outline-none focus:border-purple-600 focus:ring-1 focus:ring-purple-600 rounded-none bg-white"
        />
        <div class="flex gap-2 mt-2">
          <button type="button" @click="setPrice(80000)"
            class="text-xs px-2.5 py-1 bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors rounded-none">80.000đ</button>
          <button type="button" @click="setPrice(100000)"
            class="text-xs px-2.5 py-1 bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors rounded-none">100.000đ</button>
          <button type="button" @click="setPrice(120000)"
            class="text-xs px-2.5 py-1 bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors rounded-none">120.000đ</button>
        </div>
        <p v-if="errors.standard_price" class="mt-1 text-xs text-rose-600 font-semibold">{{ errors.standard_price }}</p>
      </div>

      <!-- Action Bar -->
      <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
        <button
          type="button"
          @click="cancelAutoGenerate"
          :disabled="isLoading"
          class="px-5 py-2.5 border border-slate-300 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition-colors rounded-none disabled:bg-slate-100 disabled:cursor-not-allowed"
        >
          Hủy
        </button>
        <button
          type="submit"
          :disabled="isLoading"
          class="px-5 py-2.5 bg-purple-600 text-white text-sm font-semibold hover:bg-purple-700 disabled:bg-slate-300 disabled:cursor-not-allowed transition-colors flex items-center gap-2 rounded-none"
        >
          <svg v-if="isLoading" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
          </svg>
          {{ isLoading ? 'Đang xử lý...' : '⚡ Bắt Đầu Xếp Lịch' }}
        </button>
      </div>

    </form>
  </div>
        `
    };

    createApp({
        components: {
            'auto-generate-showtime-form': AutoGenerateShowtimeForm
        }
    }).mount('#auto-generate-app');
})();
