<x-filament::widget>
    <x-filament::card>
        <div class="text-center">
            <h2 class="text-2xl font-bold tracking-tight sm:text-3xl">Selamat Datang, {{ auth()->user()->name }}!</h2>
            <p class="mt-2 text-lg leading-8 text-gray-600">
                Semangat bekerja! Setiap usaha kecil membawa kita lebih dekat pada tujuan besar.
            </p>
        </div>
    </x-filament::card>
</x-filament::widget>
