<x-filament::widget>
    <x-filament::card>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-4 shadow rounded">
                <h4 class="text-sm text-gray-600">Total Peserta</h4>
                <p class="text-xl font-bold text-gray-800">{{ $totalMembers }}</p>
            </div>

            <div class="bg-white p-4 shadow rounded">
                <h4 class="text-sm text-gray-600">Peserta di Pemeriksaan Terakhir</h4>
                <p class="text-xl font-bold text-gray-800">{{ $membersInLastCheckup }}</p>
            </div>

            <div class="bg-white p-4 shadow rounded">
                <h4 class="text-sm text-gray-600">Jadwal Pemeriksaan Terakhir</h4>
                <p class="text-xl font-bold text-gray-800">{{ $lastCheckupDate }}</p>
            </div>

            <div class="bg-white p-4 shadow rounded">
                <h4 class="text-sm text-gray-600">Jadwal Pemeriksaan Berikutnya</h4>
                <p class="text-xl font-bold text-gray-800">{{ $nextCheckupDate }}</p>
            </div>
        </div>
    </x-filament::card>
</x-filament::widget>
