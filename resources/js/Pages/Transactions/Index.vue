<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';

const props = defineProps({
    transactions: {
        type: Array,
        required: true,
    },
});

// Pencarian/filter
const search = ref('');

// Format tanggal ke lokal ID
function formatDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleString('id-ID', {
        day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
    });
}

// Format nominal ke Rupiah
function formatRupiah(value) {
    if (isNaN(value)) return "-";
    return "Rp " + Number(value).toLocaleString('id-ID', { minimumFractionDigits: 0 });
}

// Filtered transactions
const filteredTransactions = computed(() => {
    if (!search.value) return props.transactions;
    const s = search.value.toLowerCase();
    return props.transactions.filter(tx => {
        const nama = tx.student?.nama?.toLowerCase() || '';
        const jenis = tx.type === 'deposit' ? 'setor' : 'tarik';
        const tipe = tx.type.toLowerCase();
        const nominal = String(tx.amount);
        const ket = tx.description?.toLowerCase() || '';
        return (
            nama.includes(s) ||
            jenis.includes(s) ||
            tipe.includes(s) ||
            nominal.includes(s) ||
            ket.includes(s) ||
            formatDate(tx.created_at).toLowerCase().includes(s)
        );
    });
});
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Riwayat Transaksi</h2>
        </template>

        <div class="py-8 max-w-7xl mx-auto px-2 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-center mb-6">
                <h3 class="text-lg font-bold text-gray-700">Tabel Transaksi</h3>
                <input
                    v-model="search"
                    type="text"
                    placeholder="Cari transaksi..."
                    class="border rounded-md px-4 py-2 w-full sm:w-64 focus:ring-2 focus:ring-blue-400 focus:outline-none text-gray-700 bg-white"
                />
            </div>
            <!-- Desktop Table -->
            <div v-if="filteredTransactions.length" class="hidden md:block overflow-x-auto rounded-xl shadow-sm bg-white">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Siswa</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nominal</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <tr v-for="tx in filteredTransactions" :key="tx.id">
                            <td class="px-4 py-3 whitespace-nowrap text-gray-800">{{ formatDate(tx.created_at) }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-gray-800">{{ tx.student?.nama ?? '-' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span
                                    :class="tx.type === 'deposit' ? 'text-green-600 font-bold' : 'text-red-600 font-bold'"
                                >
                                    {{ tx.type === 'deposit' ? 'Setor' : 'Tarik' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-gray-800">{{ formatRupiah(tx.amount) }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-gray-800">{{ tx.description ?? '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-else class="hidden md:block rounded-xl shadow-sm bg-white overflow-hidden">
                <EmptyState message="Data Transaksi Tidak Ditemukan" />
            </div>
            <!-- Mobile Card List -->
            <div class="flex flex-col gap-4 md:hidden">
                <template v-if="filteredTransactions.length">
                    <div
                        v-for="tx in filteredTransactions"
                        :key="tx.id"
                        class="bg-white shadow rounded-lg px-4 py-3 flex flex-col gap-1 border border-gray-100"
                    >
                        <div class="flex items-center justify-between">
                            <span class="font-semibold text-sm text-gray-600">{{ formatDate(tx.created_at) }}</span>
                            <span
                                :class="tx.type === 'deposit' ? 'text-green-600 font-bold' : 'text-red-600 font-bold'"
                                class="text-sm"
                            >
                                {{ tx.type === 'deposit' ? 'Setor' : 'Tarik' }}
                            </span>
                        </div>
                        <div class="flex items-center mt-1">
                            <span class="text-gray-500 text-xs w-24 flex-shrink-0">Nama Siswa</span>
                            <span class="text-gray-800 text-sm ml-2">{{ tx.student?.nama ?? '-' }}</span>
                        </div>
                        <div class="flex items-center mt-1">
                            <span class="text-gray-500 text-xs w-24 flex-shrink-0">Nominal</span>
                            <span class="text-gray-800 text-sm ml-2">{{ formatRupiah(tx.amount) }}</span>
                        </div>
                        <div class="flex items-center mt-1">
                            <span class="text-gray-500 text-xs w-24 flex-shrink-0">Keterangan</span>
                            <span class="text-gray-700 text-sm ml-2">{{ tx.description ?? '-' }}</span>
                        </div>
                    </div>
                </template>
                <div v-else class="rounded-xl shadow-sm bg-white overflow-hidden">
                    <EmptyState message="Data Transaksi Tidak Ditemukan" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>