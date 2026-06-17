@extends('layouts.customer')

@section('title', 'FilmGo - Đặt Vé Xem Phim Nhanh Chóng & Tiện Lợi')

@section('content')
<!-- Hero Banner Section (Sharp Edges - No border-radius) -->
<div class="relative w-full h-[70vh] bg-black overflow-hidden select-none">
    <!-- Background Cinematic Image (Sharp Edges) -->
    <div class="absolute inset-0">
        <img src="https://images.unsplash.com/photo-1536440136628-849c177e76a1?q=80&w=1925&auto=format&fit=crop" 
             alt="Cinematic Banner" 
             class="w-full h-full object-cover opacity-60">
        <!-- Radial / Linear Gradient Overlays -->
        <div class="absolute inset-0 bg-gradient-to-t from-brand-dark via-transparent to-brand-dark/40"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-brand-dark via-brand-dark/20 to-transparent"></div>
    </div>

    <!-- Banner Content -->
    <div class="absolute inset-0 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col justify-center">
        <div class="max-w-2xl space-y-6">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded bg-brand-primary/20 border border-brand-primary text-brand-primary text-xs font-bold uppercase tracking-wider">
                <span class="material-symbols-outlined text-sm">local_fire_department</span> Phim Hot Trong Tuần
            </span>
            <h1 class="text-4xl sm:text-6xl font-black text-white leading-none uppercase tracking-tight">
                Đại Chiến <br><span class="text-brand-primary">Vũ Trụ Vô Tận</span>
            </h1>
            <p class="text-gray-300 text-base sm:text-lg leading-relaxed">
                Hành trình phiêu lưu vượt thời gian đầy kịch tính của các siêu anh hùng. Bộ phim bom tấn khoa học viễn tưởng được mong đợi nhất năm nay đã chính thức cập bến FilmGo.
            </p>
            <div class="flex flex-wrap gap-4 pt-2">
                <a href="#" class="inline-flex items-center gap-2 bg-brand-primary text-white font-bold text-sm px-8 py-3.5 hover:bg-red-700 transition-all duration-200 shadow-lg hover:shadow-red-600/30">
                    <span class="material-symbols-outlined text-lg">confirmation_number</span> Đặt Vé Ngay
                </a>
                <a href="#" class="inline-flex items-center gap-2 bg-white/10 hover:bg-white/20 border border-white/20 text-white font-semibold text-sm px-8 py-3.5 transition-all duration-200">
                    <span class="material-symbols-outlined text-lg">play_circle</span> Xem Trailer
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Area -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 space-y-20">
    
    <!-- Section: Now Showing (Phim Đang Chiếu) -->
    <div class="space-y-8">
        <div class="flex items-center justify-between border-b border-white/10 pb-4">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-brand-primary text-3xl">play_circle</span>
                <h2 class="text-2xl font-black tracking-wide uppercase text-white">Phim Đang Chiếu</h2>
            </div>
            <a href="#" class="text-sm font-semibold text-gray-400 hover:text-brand-primary flex items-center gap-1 transition-colors duration-200">
                Xem tất cả <span class="material-symbols-outlined text-sm">arrow_forward</span>
            </a>
        </div>

        <!-- Movie Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
            <!-- Movie Card 1 -->
            <div class="group flex flex-col bg-brand-secondary border border-white/5 overflow-hidden transition-all duration-300 hover:border-brand-primary/30 hover:-translate-y-1">
                <!-- Poster -->
                <div class="relative aspect-[2/3] bg-neutral-900 overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1536440136628-849c177e76a1?q=80&w=400&auto=format&fit=crop" 
                         alt="Movie Title 1" 
                         class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                    <div class="absolute top-2 left-2 px-2 py-0.5 rounded text-[10px] font-extrabold bg-red-600 text-white tracking-widest border border-red-500 shadow-md">T18</div>
                    <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                        <a href="#" class="bg-brand-primary text-white text-xs font-bold py-2.5 px-5 hover:bg-red-700 hover:scale-105 transition-all duration-200 flex items-center gap-1 shadow-md">
                            <span class="material-symbols-outlined text-sm">confirmation_number</span> ĐẶT VÉ
                        </a>
                    </div>
                </div>
                <!-- Description -->
                <div class="p-4 flex-grow flex flex-col justify-between">
                    <div>
                        <h3 class="font-bold text-white text-sm line-clamp-1 group-hover:text-brand-primary transition-colors duration-200" title="Đại Chiến Vũ Trụ Vô Tận">Đại Chiến Vũ Trụ Vô Tận</h3>
                        <p class="text-gray-400 text-xs mt-1.5 flex items-center gap-1">
                            <span class="material-symbols-outlined text-xs">schedule</span> 148 phút
                        </p>
                    </div>
                    <a href="#" class="mt-4 w-full bg-white/5 hover:bg-brand-primary hover:text-white border border-white/10 text-gray-200 text-xs font-bold py-2 text-center transition-all duration-200">
                        Chi Tiết
                    </a>
                </div>
            </div>

            <!-- Movie Card 2 -->
            <div class="group flex flex-col bg-brand-secondary border border-white/5 overflow-hidden transition-all duration-300 hover:border-brand-primary/30 hover:-translate-y-1">
                <div class="relative aspect-[2/3] bg-neutral-900 overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?q=80&w=400&auto=format&fit=crop" 
                         alt="Movie Title 2" 
                         class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                    <div class="absolute top-2 left-2 px-2 py-0.5 rounded text-[10px] font-extrabold bg-emerald-600 text-white tracking-widest border border-emerald-500 shadow-md">P</div>
                    <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                        <a href="#" class="bg-brand-primary text-white text-xs font-bold py-2.5 px-5 hover:bg-red-700 hover:scale-105 transition-all duration-200 flex items-center gap-1 shadow-md">
                            <span class="material-symbols-outlined text-sm">confirmation_number</span> ĐẶT VÉ
                        </a>
                    </div>
                </div>
                <div class="p-4 flex-grow flex flex-col justify-between">
                    <div>
                        <h3 class="font-bold text-white text-sm line-clamp-1 group-hover:text-brand-primary transition-colors duration-200" title="Vùng Đất Kỳ Diệu">Vùng Đất Kỳ Diệu</h3>
                        <p class="text-gray-400 text-xs mt-1.5 flex items-center gap-1">
                            <span class="material-symbols-outlined text-xs">schedule</span> 105 phút
                        </p>
                    </div>
                    <a href="#" class="mt-4 w-full bg-white/5 hover:bg-brand-primary hover:text-white border border-white/10 text-gray-200 text-xs font-bold py-2 text-center transition-all duration-200">
                        Chi Tiết
                    </a>
                </div>
            </div>

            <!-- Movie Card 3 -->
            <div class="group flex flex-col bg-brand-secondary border border-white/5 overflow-hidden transition-all duration-300 hover:border-brand-primary/30 hover:-translate-y-1">
                <div class="relative aspect-[2/3] bg-neutral-900 overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1478720568477-152d9b164e26?q=80&w=400&auto=format&fit=crop" 
                         alt="Movie Title 3" 
                         class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                    <div class="absolute top-2 left-2 px-2 py-0.5 rounded text-[10px] font-extrabold bg-amber-600 text-white tracking-widest border border-amber-500 shadow-md">T13</div>
                    <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                        <a href="#" class="bg-brand-primary text-white text-xs font-bold py-2.5 px-5 hover:bg-red-700 hover:scale-105 transition-all duration-200 flex items-center gap-1 shadow-md">
                            <span class="material-symbols-outlined text-sm">confirmation_number</span> ĐẶT VÉ
                        </a>
                    </div>
                </div>
                <div class="p-4 flex-grow flex flex-col justify-between">
                    <div>
                        <h3 class="font-bold text-white text-sm line-clamp-1 group-hover:text-brand-primary transition-colors duration-200" title="Thám Tử Lừng Danh">Thám Tử Lừng Danh</h3>
                        <p class="text-gray-400 text-xs mt-1.5 flex items-center gap-1">
                            <span class="material-symbols-outlined text-xs">schedule</span> 120 phút
                        </p>
                    </div>
                    <a href="#" class="mt-4 w-full bg-white/5 hover:bg-brand-primary hover:text-white border border-white/10 text-gray-200 text-xs font-bold py-2 text-center transition-all duration-200">
                        Chi Tiết
                    </a>
                </div>
            </div>

            <!-- Movie Card 4 -->
            <div class="group flex flex-col bg-brand-secondary border border-white/5 overflow-hidden transition-all duration-300 hover:border-brand-primary/30 hover:-translate-y-1">
                <div class="relative aspect-[2/3] bg-neutral-900 overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1594909122845-11baa439b7bf?q=80&w=400&auto=format&fit=crop" 
                         alt="Movie Title 4" 
                         class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                    <div class="absolute top-2 left-2 px-2 py-0.5 rounded text-[10px] font-extrabold bg-orange-600 text-white tracking-widest border border-orange-500 shadow-md">T16</div>
                    <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                        <a href="#" class="bg-brand-primary text-white text-xs font-bold py-2.5 px-5 hover:bg-red-700 hover:scale-105 transition-all duration-200 flex items-center gap-1 shadow-md">
                            <span class="material-symbols-outlined text-sm">confirmation_number</span> ĐẶT VÉ
                        </a>
                    </div>
                </div>
                <div class="p-4 flex-grow flex flex-col justify-between">
                    <div>
                        <h3 class="font-bold text-white text-sm line-clamp-1 group-hover:text-brand-primary transition-colors duration-200" title="Bí Ẩn Dưới Đáy Đại Dương">Bí Ẩn Dưới Đáy Đại Dương</h3>
                        <p class="text-gray-400 text-xs mt-1.5 flex items-center gap-1">
                            <span class="material-symbols-outlined text-xs">schedule</span> 135 phút
                        </p>
                    </div>
                    <a href="#" class="mt-4 w-full bg-white/5 hover:bg-brand-primary hover:text-white border border-white/10 text-gray-200 text-xs font-bold py-2 text-center transition-all duration-200">
                        Chi Tiết
                    </a>
                </div>
            </div>

            <!-- Movie Card 5 -->
            <div class="group flex flex-col bg-brand-secondary border border-white/5 overflow-hidden transition-all duration-300 hover:border-brand-primary/30 hover:-translate-y-1">
                <div class="relative aspect-[2/3] bg-neutral-900 overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1509198397868-475647b2a1e5?q=80&w=400&auto=format&fit=crop" 
                         alt="Movie Title 5" 
                         class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                    <div class="absolute top-2 left-2 px-2 py-0.5 rounded text-[10px] font-extrabold bg-blue-600 text-white tracking-widest border border-blue-500 shadow-md">K</div>
                    <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                        <a href="#" class="bg-brand-primary text-white text-xs font-bold py-2.5 px-5 hover:bg-red-700 hover:scale-105 transition-all duration-200 flex items-center gap-1 shadow-md">
                            <span class="material-symbols-outlined text-sm">confirmation_number</span> ĐẶT VÉ
                        </a>
                    </div>
                </div>
                <div class="p-4 flex-grow flex flex-col justify-between">
                    <div>
                        <h3 class="font-bold text-white text-sm line-clamp-1 group-hover:text-brand-primary transition-colors duration-200" title="Chuyến Phiêu Lưu Nhí">Chuyến Phiêu Lưu Nhí</h3>
                        <p class="text-gray-400 text-xs mt-1.5 flex items-center gap-1">
                            <span class="material-symbols-outlined text-xs">schedule</span> 90 phút
                        </p>
                    </div>
                    <a href="#" class="mt-4 w-full bg-white/5 hover:bg-brand-primary hover:text-white border border-white/10 text-gray-200 text-xs font-bold py-2 text-center transition-all duration-200">
                        Chi Tiết
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Section: Coming Soon (Phim Sắp Chiếu) -->
    <div class="space-y-8">
        <div class="flex items-center justify-between border-b border-white/10 pb-4">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-brand-primary text-3xl">upcoming</span>
                <h2 class="text-2xl font-black tracking-wide uppercase text-white">Phim Sắp Chiếu</h2>
            </div>
            <a href="#" class="text-sm font-semibold text-gray-400 hover:text-brand-primary flex items-center gap-1 transition-colors duration-200">
                Xem tất cả <span class="material-symbols-outlined text-sm">arrow_forward</span>
            </a>
        </div>

        <!-- Movie Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
            <!-- Movie Card 1 (Upcoming) -->
            <div class="group flex flex-col bg-brand-secondary border border-white/5 overflow-hidden transition-all duration-300 hover:border-brand-primary/30 hover:-translate-y-1">
                <div class="relative aspect-[2/3] bg-neutral-900 overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1542204172-e7052809f852?q=80&w=400&auto=format&fit=crop" 
                         alt="Upcoming 1" 
                         class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                    <div class="absolute top-2 left-2 px-2 py-0.5 rounded text-[10px] font-extrabold bg-amber-600 text-white tracking-widest border border-amber-500 shadow-md">T13</div>
                </div>
                <div class="p-4 flex-grow flex flex-col justify-between">
                    <div>
                        <h3 class="font-bold text-white text-sm line-clamp-1 group-hover:text-brand-primary transition-colors duration-200" title="Chiến Binh Rừng Xanh">Chiến Binh Rừng Xanh</h3>
                        <p class="text-gray-400 text-xs mt-1.5 flex items-center gap-1">
                            <span class="material-symbols-outlined text-xs">calendar_month</span> Khởi chiếu: 15/07/2026
                        </p>
                    </div>
                    <div class="mt-4 flex gap-2">
                        <a href="#" class="flex-1 bg-white/5 hover:bg-white/10 text-white text-xs font-bold py-2 text-center transition-all duration-200 border border-white/10">Trailer</a>
                        <a href="#" class="flex-grow bg-brand-primary/10 hover:bg-brand-primary hover:text-white border border-brand-primary/20 text-brand-primary text-xs font-bold py-2 text-center transition-all duration-200">Chi Tiết</a>
                    </div>
                </div>
            </div>

            <!-- Movie Card 2 (Upcoming) -->
            <div class="group flex flex-col bg-brand-secondary border border-white/5 overflow-hidden transition-all duration-300 hover:border-brand-primary/30 hover:-translate-y-1">
                <div class="relative aspect-[2/3] bg-neutral-900 overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=400&auto=format&fit=crop" 
                         alt="Upcoming 2" 
                         class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                    <div class="absolute top-2 left-2 px-2 py-0.5 rounded text-[10px] font-extrabold bg-red-600 text-white tracking-widest border border-red-500 shadow-md">T18</div>
                </div>
                <div class="p-4 flex-grow flex flex-col justify-between">
                    <div>
                        <h3 class="font-bold text-white text-sm line-clamp-1 group-hover:text-brand-primary transition-colors duration-200" title="Sát Thủ Bóng Đêm">Sát Thủ Bóng Đêm</h3>
                        <p class="text-gray-400 text-xs mt-1.5 flex items-center gap-1">
                            <span class="material-symbols-outlined text-xs">calendar_month</span> Khởi chiếu: 22/07/2026
                        </p>
                    </div>
                    <div class="mt-4 flex gap-2">
                        <a href="#" class="flex-1 bg-white/5 hover:bg-white/10 text-white text-xs font-bold py-2 text-center transition-all duration-200 border border-white/10">Trailer</a>
                        <a href="#" class="flex-grow bg-brand-primary/10 hover:bg-brand-primary hover:text-white border border-brand-primary/20 text-brand-primary text-xs font-bold py-2 text-center transition-all duration-200">Chi Tiết</a>
                    </div>
                </div>
            </div>

            <!-- Movie Card 3 (Upcoming) -->
            <div class="group flex flex-col bg-brand-secondary border border-white/5 overflow-hidden transition-all duration-300 hover:border-brand-primary/30 hover:-translate-y-1">
                <div class="relative aspect-[2/3] bg-neutral-900 overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?q=80&w=400&auto=format&fit=crop" 
                         alt="Upcoming 3" 
                         class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                    <div class="absolute top-2 left-2 px-2 py-0.5 rounded text-[10px] font-extrabold bg-emerald-600 text-white tracking-widest border border-emerald-500 shadow-md">P</div>
                </div>
                <div class="p-4 flex-grow flex flex-col justify-between">
                    <div>
                        <h3 class="font-bold text-white text-sm line-clamp-1 group-hover:text-brand-primary transition-colors duration-200" title="Thành Phố Khuyết Danh">Thành Phố Khuyết Danh</h3>
                        <p class="text-gray-400 text-xs mt-1.5 flex items-center gap-1">
                            <span class="material-symbols-outlined text-xs">calendar_month</span> Khởi chiếu: 30/07/2026
                        </p>
                    </div>
                    <div class="mt-4 flex gap-2">
                        <a href="#" class="flex-1 bg-white/5 hover:bg-white/10 text-white text-xs font-bold py-2 text-center transition-all duration-200 border border-white/10">Trailer</a>
                        <a href="#" class="flex-grow bg-brand-primary/10 hover:bg-brand-primary hover:text-white border border-brand-primary/20 text-brand-primary text-xs font-bold py-2 text-center transition-all duration-200">Chi Tiết</a>
                    </div>
                </div>
            </div>

            <!-- Movie Card 4 (Upcoming) -->
            <div class="group flex flex-col bg-brand-secondary border border-white/5 overflow-hidden transition-all duration-300 hover:border-brand-primary/30 hover:-translate-y-1">
                <div class="relative aspect-[2/3] bg-neutral-900 overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1501854140801-50d01698950b?q=80&w=400&auto=format&fit=crop" 
                         alt="Upcoming 4" 
                         class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                    <div class="absolute top-2 left-2 px-2 py-0.5 rounded text-[10px] font-extrabold bg-orange-600 text-white tracking-widest border border-orange-500 shadow-md">T16</div>
                </div>
                <div class="p-4 flex-grow flex flex-col justify-between">
                    <div>
                        <h3 class="font-bold text-white text-sm line-clamp-1 group-hover:text-brand-primary transition-colors duration-200" title="Dòng Sông Ký Ức">Dòng Sông Ký Ức</h3>
                        <p class="text-gray-400 text-xs mt-1.5 flex items-center gap-1">
                            <span class="material-symbols-outlined text-xs">calendar_month</span> Khởi chiếu: 05/08/2026
                        </p>
                    </div>
                    <div class="mt-4 flex gap-2">
                        <a href="#" class="flex-1 bg-white/5 hover:bg-white/10 text-white text-xs font-bold py-2 text-center transition-all duration-200 border border-white/10">Trailer</a>
                        <a href="#" class="flex-grow bg-brand-primary/10 hover:bg-brand-primary hover:text-white border border-brand-primary/20 text-brand-primary text-xs font-bold py-2 text-center transition-all duration-200">Chi Tiết</a>
                    </div>
                </div>
            </div>

            <!-- Movie Card 5 (Upcoming) -->
            <div class="group flex flex-col bg-brand-secondary border border-white/5 overflow-hidden transition-all duration-300 hover:border-brand-primary/30 hover:-translate-y-1">
                <div class="relative aspect-[2/3] bg-neutral-900 overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1518709268805-4e9042af9f23?q=80&w=400&auto=format&fit=crop" 
                         alt="Upcoming 5" 
                         class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                    <div class="absolute top-2 left-2 px-2 py-0.5 rounded text-[10px] font-extrabold bg-blue-600 text-white tracking-widest border border-blue-500 shadow-md">K</div>
                </div>
                <div class="p-4 flex-grow flex flex-col justify-between">
                    <div>
                        <h3 class="font-bold text-white text-sm line-clamp-1 group-hover:text-brand-primary transition-colors duration-200" title="Chiến Hạm Đại Dương">Chiến Hạm Đại Dương</h3>
                        <p class="text-gray-400 text-xs mt-1.5 flex items-center gap-1">
                            <span class="material-symbols-outlined text-xs">calendar_month</span> Khởi chiếu: 12/08/2026
                        </p>
                    </div>
                    <div class="mt-4 flex gap-2">
                        <a href="#" class="flex-1 bg-white/5 hover:bg-white/10 text-white text-xs font-bold py-2 text-center transition-all duration-200 border border-white/10">Trailer</a>
                        <a href="#" class="flex-grow bg-brand-primary/10 hover:bg-brand-primary hover:text-white border border-brand-primary/20 text-brand-primary text-xs font-bold py-2 text-center transition-all duration-200">Chi Tiết</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
