<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import EmptyState from '@/Components/EmptyState.vue'
import { Link } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({
    student: {
        type: Object,
        required: true,
    },
})

function formatDate(dateStr) {
    const date = new Date(dateStr)
    return date.toLocaleString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    })
}

function formatRupiah(value) {
    if (isNaN(value)) return "-"
    return "Rp " + Number(value).toLocaleString('id-ID', { minimumFractionDigits: 0 })
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detail Siswa</h2>
        </template>

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Card Profil Siswa -->
            <div class="bg-white rounded-lg shadow mb-8 p-6 flex flex-col sm:flex-row items-center justify-between gap-6">
                <div class="flex flex-col gap-2">
                    <div>
                        <span class="text-gray-400 font-medium">Nama:</span>
                        <span class="ml-1 font-semibold text-gray-800">{{ props.student.nama }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 font-medium">NIS:</span>
                        <span class="ml-1 text-gray-800">{{ props.student.nis }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 font-medium">Kelas:</span>
                        <span class="ml-1 text-gray-800">{{ props.student.kelas }}</span>
                    </div>
                </div>
                <div class="flex flex-col items-center sm:items-end">
                    <span class="text-gray-400 font-medium">Total Saldo</span>
                    <span class="text-2xl font-bold text-emerald-600">{{ formatRupiah(props.student.saldo) }}</span>
                </div>
            </div>

            <!-- Tombol Kembali -->
            <div class="mb-6">
                <Link
                    :href="route('students.index')"
                    class="inline-block bg-gray-100 text-gray-700 hover:bg-gray-200 hover:text-gray-900 px-4 py-2 rounded transition font-medium shadow"
                >
                    ← Kembali
                </Link>
            </div>

            <!-- Tabel Riwayat Transaksi Pribadi -->
            <div class="bg-white rounded-xl shadow-sm p-6 overflow-hidden">
                <h3 class="text-lg font-bold text-gray-700 mb-4">Riwayat Transaksi Pribadi</h3>
                <div v-if="(props.student.transactions || []).length" class="overflow-x-auto rounded-xl">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nominal</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            <tr v-for="tx in (props.student.transactions || [])" :key="tx.id">
                                <td class="px-4 py-3 whitespace-nowrap text-gray-800">{{ formatDate(tx.created_at) }}</td>
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
                <EmptyState v-else message="Riwayat Transaksi Tidak Ditemukan" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>