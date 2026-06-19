@extends('layouts.manager')

@section('title', 'Rạp Của Tôi')

@section('content')

    <div class="space-y-6">

        <div>
            <h1 class="text-2xl font-bold">
                Rạp Của Tôi
            </h1>

            <p class="text-slate-500">
                Danh sách rạp được phân công quản lý
            </p>
        </div>

        <div class="grid gap-4">

            @forelse($cinemas as $cinema)
                <div class="bg-white border rounded-lg p-5 shadow-sm">

                    <h3 class="font-bold text-lg">
                        {{ $cinema->name }}
                    </h3>

                    <p class="text-slate-500 mt-2">
                        {{ $cinema->address }}
                    </p>

                </div>

            @empty

                <div class="bg-white rounded-lg p-6 text-center">

                    Chưa được phân công rạp nào

                </div>
            @endforelse

        </div>

    </div>

@endsection
