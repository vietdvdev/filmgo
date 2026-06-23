/**
 * seat-map.js — Entry point cho tính năng Thiết lập Sơ đồ Ghế
 *
 * Được nhúng vào Blade view của Manager qua:
 *   @vite(['resources/js/seat-map.js'])
 *
 * Cách dùng trong Blade:
 *   <div id="seat-map-app"
 *        data-room-id="{{ $room->id }}"
 *        data-sync-path="/manager/rooms/{{ $room->id }}/sync-seats"
 *        data-seat-types="{{ json_encode($seatTypes) }}"
 *        data-seats="{{ json_encode($room->seats) }}">
 *   </div>
 */

import { createApp } from 'vue'
import SeatMapBuilder from './components/SeatMapBuilder.vue'

const mountEl = document.getElementById('seat-map-app')

if (mountEl) {
    // Lấy dữ liệu được truyền từ Blade qua data attributes
    const roomId       = mountEl.dataset.roomId
    // data-sync-url: URL đầy đủ được tạo từ Blade dùng request()->getSchemeAndHttpHost() + getBaseUrl()
    // đảm bảo đúng cả khi chạy trong thư mục con hoặc virtual host
    const syncUrl      = mountEl.dataset.syncUrl
    const seatTypes    = JSON.parse(mountEl.dataset.seatTypes ?? '[]')
    const initialSeats = JSON.parse(mountEl.dataset.seats ?? '[]')

    const app = createApp(SeatMapBuilder, {
        roomId,
        syncUrl,
        seatTypes,
        initialSeats,
    })

    app.mount(mountEl)
}
