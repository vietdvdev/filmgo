/**
 * FilmGo — Auto Generate Showtime Component & App
 * Vue 3 CDN (global build)
 * Version 2.0 — Fixed: multi-cinema support, auth check, validation feedback
 */
(function () {
    const { createApp, ref, reactive, onMounted, watch } = Vue;
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
                default: '/manager/api/admin/my-cinemas'
            },
            roomsUrlPattern: {
                type: String,
                default: '/manager/api/admin/cinemas/:cinema_id/rooms'
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

            const isLoading    = ref(false);
            const isLoadingCinemas = ref(true);
            const isLoadingRooms   = ref(false);
            const errors       = ref({});
            const globalSuccessMessage = ref('');
            const globalErrorMessage   = ref('');

            const moviesList   = ref(movies);
            const cinemasList  = ref([]);
            const roomsList    = ref([]);

            // Khi chọn rạp → load phòng tương ứng
            const selectedCinemaId = ref('');

            onMounted(async () => {
                await fetchCinemas();
            });

            /**
             * Lấy danh sách rạp của Manager đang đăng nhập
             */
            const fetchCinemas = async () => {
                isLoadingCinemas.value = true;
                try {
                    const res = await axios.get(props.myCinemasUrl);
                    cinemasList.value = res.data || [];

                    // Tự động chọn rạp đầu tiên nếu chỉ có 1 rạp
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

            /**
             * Lấy danh sách phòng khi Manager chọn rạp
             */
            const fetchRoomsByCinema = async (cinemaId) => {
                if (!cinemaId) {
                    roomsList.value = [];
                    payload.room_id = '';
                    return;
                }
                isLoadingRooms.value = true;
                roomsList.value = [];
                payload.room_id = '';
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

            // Watch khi Manager đổi lựa chọn rạp → reset phòng và load lại
            watch(selectedCinemaId, async (newCinemaId) => {
                await fetchRoomsByCinema(newCinemaId);
            });

            const setPrice = (amount) => {
                payload.standard_price = amount;
            };

            const submitAutoGenerate = async () => {
                // Kiểm tra phía client trước khi gửi
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

                // Dừng nếu có lỗi phía client
                if (Object.keys(errors.value).length > 0) {
                    globalErrorMessage.value = 'Vui lòng kiểm tra lại thông tin các trường lỗi.';
                    return;
                }

                isLoading.value = true;

                try {
                    const res = await axios.post(props.autoGenerateUrl, payload);

                    if (res.data && res.data.success) {
                        // Lưu thông báo thành công vào sessionStorage để hiển thị sau khi reload trang
                        sessionStorage.setItem('showtime_success_message', `Đã tự động xếp thành công ${res.data.total_generated} suất chiếu mới cho ngày ${payload.show_date}!`);
                        // Chuyển hướng về trang danh sách suất chiếu với tham số ngày đã chọn
                        window.location.href = `${props.cancelUrl}?date=${payload.show_date}`;
                    }
                } catch (error) {
                    const status = error.response ? error.response.status : null;
                    const data   = error.response ? error.response.data : null;

                    if (status === 422 && data && data.errors) {
                        // Validation errors từ server
                        Object.entries(data.errors).forEach(([field, msg]) => {
                            errors.value[field] = Array.isArray(msg) ? msg[0] : msg;
                        });
                        globalErrorMessage.value = 'Vui lòng kiểm tra lại thông tin các trường lỗi.';
                    } else if (status === 403) {
                        globalErrorMessage.value = data && data.message
                            ? data.message
                            : 'Bạn không có quyền thực hiện thao tác này.';
                    } else if (status === 400 && data && data.message) {
                        // Không tìm được slot trống
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

            return {
                payload, isLoading, isLoadingCinemas, isLoadingRooms,
                errors, globalSuccessMessage, globalErrorMessage,
                moviesList, cinemasList, roomsList,
                selectedCinemaId, todayDate,
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

        <!-- Block 2 (Base Info — Grid 3 cols) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <!-- Chọn Phim -->
          <div>
            <label for="movie_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
              Chọn Phim
            </label>
            <select
              id="movie_id"
              v-model="payload.movie_id"
              class="block w-full px-3 py-2.5 border text-sm focus:outline-none focus:border-purple-600 focus:ring-1 focus:ring-purple-600 bg-white rounded-none disabled:bg-slate-100 disabled:text-slate-400"
              :class="errors.movie_id ? 'border-rose-400' : 'border-slate-300'"
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
            <div class="relative">
              <select
                id="room_id"
                v-model="payload.room_id"
                :disabled="isLoadingRooms || roomsList.length === 0"
                class="block w-full px-3 py-2.5 border text-sm focus:outline-none focus:border-purple-600 focus:ring-1 focus:ring-purple-600 bg-white rounded-none disabled:bg-slate-100 disabled:text-slate-400"
                :class="errors.room_id ? 'border-rose-400' : 'border-slate-300'"
              >
                <option value="">{{ isLoadingRooms ? 'Đang tải phòng...' : (roomsList.length === 0 ? 'Không có phòng hoạt động' : '-- Chọn phòng --') }}</option>
                <option v-for="room in roomsList" :key="room.id" :value="room.id">
                  {{ room.room_name }} ({{ room.room_type }} – {{ room.capacity }} ghế)
                </option>
              </select>
              <div v-if="isLoadingRooms" class="absolute right-3 top-1/2 -translate-y-1/2">
                <svg class="animate-spin h-4 w-4 text-purple-500" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
              </div>
            </div>
            <p v-if="errors.room_id" class="mt-1 text-xs text-rose-600 font-semibold">{{ errors.room_id }}</p>
            <p v-if="!isLoadingRooms && roomsList.length === 0 && selectedCinemaId" class="mt-1 text-xs text-amber-600">
              Rạp này chưa có phòng chiếu nào đang hoạt động.
            </p>
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
          <p v-if="errors.standard_price" class="mt-1 text-xs text-rose-600 font-semibold">{{ errors.standard_price }}</p>
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
        `
    };

    createApp({
        components: {
            'auto-generate-showtime-form': AutoGenerateShowtimeForm
        }
    }).mount('#auto-generate-app');
})();
