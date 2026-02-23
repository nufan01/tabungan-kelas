<script setup>
defineProps({
    message: {
        type: String,
        default: 'Data Tidak Ditemukan',
    },
});
</script>

<template>
    <div class="flex flex-col items-center justify-center py-12 px-4 text-center">
        <div class="w-20 h-20 mb-4 flex items-center justify-center rounded-full bg-gray-100 text-gray-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
        </div>
        <p class="text-gray-500 text-base font-medium">{{ message }}</p>
        <p class="text-gray-400 text-sm mt-1">Belum ada data untuk ditampilkan</p>
    </div>
</template>
