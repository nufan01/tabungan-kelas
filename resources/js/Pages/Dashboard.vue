<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

// Props dari DashboardController
const props = defineProps({
    totalBalance: { type: Number, required: true },
    totalStudents: { type: Number, required: true },
    todayTransactions: { type: Number, required: true },
    recentTransactions: { type: Array, required: true },
});

// Helper format Rupiah
function formatRupiah(value) {
    if (isNaN(value)) return "-";
    return "Rp " + Number(value).toLocaleString('id-ID', { minimumFractionDigits: 0 });
}

// Format tanggal & jam pendek
function formatDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

// Pencarian Siswa
const search = ref('');
function goToStudentSearch() {
    if (search.value.trim() !== '') {
        router.visit(`/students?search=${encodeURIComponent(search.value.trim())}`);
    }
}
</script>

<template>
    <Head title="Dashboard Statistik Keuangan" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Dashboard Statistik Keuangan</h2>
                <!-- Kolom Pencarian Siswa -->
                <form
                    class="flex flex-col xs:flex-row items-stretch xs:items-center mt-2 md:mt-0 gap-2 xs:gap-2"
                    @submit.prevent="goToStudentSearch"
                >
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cari siswa..."
                        class="block w-full xs:w-44 sm:w-56 rounded border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm shadow-sm"
                        @keyup.enter="goToStudentSearch"
                    />
                    <button
                        type="submit"
                        class="w-full xs:w-auto px-3 py-1.5 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition text-sm font-semibold"
                    >
                        Cari
                    </button>
                </form>
            </div>
        </template>

        <div class="py-6 sm:py-10">
            <div class="mx-auto max-w-7xl px-2 xs:px-4 sm:px-6 lg:px-8">
                <!-- Statistik Grid Card -->
                <div class="grid grid-cols-2 gap-4 px-4 py-6">
    
    <div class="col-span-2 bg-white rounded-2xl p-5 shadow-sm border border-gray-50 flex items-center space-x-4">
        <div class="flex-shrink-0 w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center">
            <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div>
            <p class="text-[10px] uppercase font-bold text-gray-400 tracking-tight">Total Saldo Kas</p>
            <h2 class="text-xl font-extrabold text-emerald-700 leading-tight">
                {{ formatRupiah(totalBalance) }}
            </h2>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-50 flex flex-col items-start justify-between">
        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mb-3">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
        </div>
        <div>
            <p class="text-[10px] uppercase font-bold text-gray-400">Siswa</p>
            <h2 class="text-lg font-bold text-gray-800">{{ totalStudents }}</h2>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-50 flex flex-col items-start justify-between">
        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mb-3">
            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
            </svg>
        </div>
        <div>
            <p class="text-[10px] uppercase font-bold text-gray-400">Transaksi</p>
            <h2 class="text-lg font-bold text-gray-800">{{ todayTransactions }}</h2>
        </div>
    </div>

</div>

                <!-- Tabel Aktivitas Terbaru -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mt-4 sm:mt-6 md:mt-8 mb-2 sm:mb-4 md:mb-6 px-2 xs:px-4 sm:px-6 py-4 sm:py-6">
                    <div class="pb-4 sm:pb-6 border-b border-gray-50 flex items-center">
                        <h3 class="text-lg sm:text-xl font-semibold sm:font-bold text-gray-800 tracking-tight leading-tight">Aktivitas Terbaru</h3>
                    </div>
                    <!-- Mobile: Card style, Desktop: Table -->
                    <div class="block md:hidden">
                        <div v-if="props.recentTransactions.length">
                            <div v-for="tx in props.recentTransactions.slice(0, 5)" :key="tx.id" class="border-b last:border-b-0 border-gray-100 py-4 px-2 sm:px-4 flex flex-col gap-2">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-xs font-medium text-gray-500 tracking-tight">{{ formatDate(tx.created_at) }}</span>
                                    <span
                                        :class="[
                                            'rounded-full px-2 py-0.5 text-xs font-semibold',
                                            tx.type === 'deposit' ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600'
                                        ]"
                                    >{{ tx.type === 'deposit' ? 'Setor' : 'Tarik' }}</span>
                                </div>
                                <div class="font-medium text-gray-900 text-sm sm:text-base truncate">{{ tx.student?.nama ?? '-' }}</div>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-emerald-700 text-base">{{ formatRupiah(tx.amount) }}</span>
                                    <span class="text-xs text-gray-400 italic truncate">{{ tx.description ?? '-' }}</span>
                                </div>
                            </div>
                        </div>
                        <div v-else class="px-2 sm:px-4 py-6">
                            <EmptyState message="Aktivitas Terbaru Tidak Ditemukan" />
                        </div>
                    </div>
                    <div v-if="props.recentTransactions.length" class="hidden md:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal</th>
                                    <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Siswa</th>
                                    <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Jenis</th>
                                    <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nominal</th>
                                    <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <tr v-for="tx in props.recentTransactions.slice(0, 5)" :key="tx.id" class="hover:bg-gray-50 transition">
                                    <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-gray-700 text-sm tracking-tight">
                                        {{ formatDate(tx.created_at) }}
                                    </td>
                                    <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-gray-900 font-medium text-sm sm:text-base">
                                        {{ tx.student?.nama ?? '-' }}
                                    </td>
                                    <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                        <span
                                            :class="[
                                                'rounded-full px-2 py-1 text-xs font-semibold',
                                                tx.type === 'deposit'
                                                    ? 'text-green-700 bg-green-50'
                                                    : 'text-red-700 bg-red-50'
                                            ]"
                                        >
                                            {{ tx.type === 'deposit' ? 'Setor' : 'Tarik' }}
                                        </span>
                                    </td>
                                    <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-emerald-700 font-bold text-sm sm:text-base">
                                        {{ formatRupiah(tx.amount) }}
                                    </td>
                                    <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-gray-600 italic text-sm">
                                        {{ tx.description ?? '-' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="hidden md:block px-4 sm:px-6 py-8">
                        <EmptyState message="Aktivitas Terbaru Tidak Ditemukan" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
