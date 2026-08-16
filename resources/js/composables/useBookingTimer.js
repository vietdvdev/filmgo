import { ref, computed } from 'vue';
import axios from 'axios';

/**
 * Composable / Store quản lý bộ đếm thời gian giữ ghế 5 phút liên tục trong FilmGo.
 * Lưu trữ timestamp hết hạn vào LocalStorage để không bị reset khi chuyển route hoặc reload trang.
 */
export function useBookingTimer() {
    const STORAGE_KEY = 'filmgo_booking_expires_at';
    const SHOWTIME_KEY = 'filmgo_booking_showtime_id';

    const expiresAt = ref(Number(localStorage.getItem(STORAGE_KEY)) || null);
    const showtimeId = ref(localStorage.getItem(SHOWTIME_KEY) || null);
    const timeLeft = ref(0);
    const isExpired = ref(false);
    let intervalId = null;

    /**
     * Bắt đầu đếm ngược 5 phút
     * @param {number} timestampMs Mốc thời gian hết hạn (Unix timestamp tính bằng milliseconds)
     * @param {number|string} sId ID suất chiếu hiện tại
     */
    const startTimer = (timestampMs, sId) => {
        expiresAt.value = timestampMs;
        showtimeId.value = sId;
        isExpired.value = false;

        localStorage.setItem(STORAGE_KEY, timestampMs.toString());
        localStorage.setItem(SHOWTIME_KEY, sId.toString());

        updateRemaining();

        if (intervalId) clearInterval(intervalId);
        intervalId = setInterval(() => {
            updateRemaining();
        }, 1000);
    };

    /**
     * Cập nhật thời gian còn lại
     */
    const updateRemaining = async () => {
        if (!expiresAt.value) {
            timeLeft.value = 0;
            return;
        }

        const now = Date.now();
        const diff = expiresAt.value - now;

        if (diff <= 0) {
            timeLeft.value = 0;
            isExpired.value = true;
            stopTimer();
            await handleTimeout();
        } else {
            timeLeft.value = diff;
        }
    };

    /**
     * Dừng đếm ngược và dọn dẹp LocalStorage
     */
    const stopTimer = () => {
        if (intervalId) {
            clearInterval(intervalId);
            intervalId = null;
        }
        localStorage.removeItem(STORAGE_KEY);
        localStorage.removeItem(SHOWTIME_KEY);
        expiresAt.value = null;
    };

    /**
     * Gọi API nhả ghế trong cơ sở dữ liệu ngay lập tức
     * @param {'seats'|'home'} redirectTo Mục tiêu điều hướng
     */
    const releaseSeatsAPI = async (redirectTo = 'seats') => {
        const sId = showtimeId.value;
        if (!sId) return;

        try {
            await axios.post(`/api/booking/showtime/${sId}/release-seats`, {
                redirect_to: redirectTo,
            }, {
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                }
            });
        } catch (error) {
            console.error('[BookingTimer] Lỗi khi gọi API nhả ghế:', error);
        } finally {
            stopTimer();
        }
    };

    /**
     * Xử lý khi hết thời gian giữ ghế 5 phút
     */
    const handleTimeout = async () => {
        alert('Thời gian giữ ghế đã hết! Phiên đặt vé của bạn đã bị hủy.');
        await releaseSeatsAPI('home');
        window.location.href = '/';
    };

    /**
     * Chuỗi định dạng hiển thị mm:ss
     */
    const formattedTime = computed(() => {
        if (timeLeft.value <= 0) return '00:00';
        const totalSeconds = Math.floor(timeLeft.value / 1000);
        const minutes = Math.floor(totalSeconds / 60);
        const seconds = totalSeconds % 60;
        return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    });

    /**
     * Cảnh báo thời gian sắp hết (còn dưới 60 giây)
     */
    const isWarning = computed(() => {
        return timeLeft.value > 0 && timeLeft.value <= 60000;
    });

    return {
        expiresAt,
        showtimeId,
        timeLeft,
        isExpired,
        formattedTime,
        isWarning,
        startTimer,
        stopTimer,
        releaseSeatsAPI,
    };
}
