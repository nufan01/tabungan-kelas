<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import axios from 'axios';

const props = defineProps({
    totalBalance: { type: Number, required: true },
    canLogin: Boolean,
    totalKas: Number, // Pastikan ini datang dari (Sum Deposit - Sum Withdrawal)
    totalSiswa: Number,
    topStudents: Array,
});

// Helper Format Rupiah agar presisi
const formatRupiah = (number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(number);
};

// Logic Cek Saldo Mandiri
const searchId = ref('');
const searchResult = ref(null);
const errorMessage = ref('');
const isSearching = ref(false);

const handleCheckSaldo = async () => {
    if (!searchId.value) return;
    
    isSearching.value = true;
    searchResult.value = null;
    errorMessage.value = '';

    try {
        const response = await axios.post(route('cek.saldo'), {
            identifier: searchId.value
        });
        searchResult.value = response.data;
    } catch (error) {
        errorMessage.value = error.response?.data?.message || 'NIS tidak ditemukan';
    } finally {
        isSearching.value = false;
    }
};
</script>

<template>
    <Head title="Selamat Datang - Tabungan Kelas" />

    <div class="min-h-screen bg-gray-50 selection:bg-indigo-500 selection:text-white">
        <nav class="bg-white border-b border-gray-100 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 items-center">
                    <div class="flex items-center gap-2">
                        <div class="flex shrink-0 items-center">
                            <Link :href="route('dashboard')">
                                <div class="flex items-center gap-3 group">
                                    <svg viewBox="0 0 100 100" class="w-10 h-10 drop-shadow-sm flex-shrink-0" xmlns="http://www.w3.org/2000/svg">
                                        <defs>
                                            <linearGradient id="logoGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                                <stop offset="0%" style="stop-color:#4f46e5" />
                                                <stop offset="100%" style="stop-color:#9333ea" />
                                            </linearGradient>
                                        </defs>
                                        <path d="M20,55 C20,35 35,25 55,25 C75,25 85,35 85,55 C85,70 75,80 55,80 C45,80 35,78 20,70 L20,55" fill="url(#logoGrad)" />
                                        <path d="M50,25 L60,15 L70,25" fill="#4f46e5" />
                                        <rect x="50" y="35" width="10" height="3" rx="1.5" fill="white" />
                                        <path d="M45,20 L65,20 L75,30 L55,30 Z" fill="#1e1b4b" />
                                        <path d="M60,30 L60,35" stroke="#1e1b4b" stroke-width="2" />
                                        <circle cx="75" cy="50" r="2" fill="white" />
                                    </svg>
                                    <div class="flex flex-col leading-none">
                                        <span class="text-[20px] font-black uppercase tracking-widest text-gray-1500">
                                            Simpanan
                                          <span class="text-[20px] font-black uppercase tracking-widest text-indigo-800">
                                            Siswa
                                          </span>
                                        </span>
                                    </div>
                                </div>
                            </Link>
                        </div>
                    </div>
                    <div v-if="canLogin">
                        <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="text-indigo-600 font-medium">Dashboard</Link>
                        <Link v-else :href="route('login')" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700 transition">Login Bendahara</Link>
                    </div>
                </div>
            </div>
        </nav>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                
                <div>
                    <h1 class="text-5xl font-extrabold text-gray-900 leading-tight mb-6">
                        Tabungan Kelas <br><span class="text-indigo-600">Lebih Transparan.</span>
                    </h1>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                            <p class="text-sm font-medium text-gray-500 mb-1">Total Saldo Kas</p>
                            <h2 class="text-2xl font-bold text-emerald-600">{{ formatRupiah(totalBalance ) }}</h2>
                        </div>
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                            <p class="text-sm font-medium text-gray-500 mb-1">Total Penabung</p>
                            <h2 class="text-2xl font-bold text-gray-900">{{ totalSiswa }} Siswa</h2>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <span>🏆</span> Penabung Teraktif
                        </h3>
                        <div class="space-y-4">
                            <div v-for="(student, index) in topStudents" :key="index" class="flex justify-between items-center">
                                <span class="text-gray-600">{{ index + 1 }}. {{ student.nama }}</span>
                                <span class="font-semibold text-gray-900">{{ formatRupiah(student.saldo) }}</span>
                            </div>
                            <p v-if="!topStudents.length" class="text-gray-400 text-sm italic">Belum ada data transaksi.</p>
                        </div>
                    </div>
                </div>

                <div class="bg-indigo-600 rounded-3xl p-8 shadow-2xl shadow-indigo-200 relative overflow-hidden">
                    <div class="relative z-10 text-white">
                        <h2 class="text-2xl font-bold mb-2">Cek Saldo Kamu</h2>
                        <p class="text-indigo-100 text-sm mb-6">Masukkan NIS untuk melihat saldo tabunganmu secara real-time.</p>
                        
                        <div class="space-y-3">
                            <input 
                                v-model="searchId"
                                type="text" 
                                placeholder="Masukkan NIS Siswa..." 
                                class="w-full px-5 py-4 rounded-xl text-gray-900 border-none focus:ring-4 focus:ring-indigo-400"
                                @keyup.enter="handleCheckSaldo"
                            />
                            <button 
                                @click="handleCheckSaldo"
                                :disabled="isSearching"
                                class="w-full bg-white text-indigo-600 font-bold py-4 rounded-xl hover:bg-indigo-50 transition active:scale-[0.98] disabled:opacity-70"
                            >
                                {{ isSearching ? 'Mencari...' : 'Cek Saldo Sekarang' }}
                            </button>
                        </div>

                        <div v-if="searchResult" class="mt-8 p-6 bg-white/10 rounded-2xl border border-white/20 animate-in fade-in slide-in-from-bottom-4 duration-500">
                            <p class="text-xs uppercase tracking-widest text-indigo-200 font-bold mb-1">Nama Siswa</p>
                            <p class="text-xl font-bold mb-4">{{ searchResult.nama }}</p>
                            <div class="h-px bg-white/20 w-full mb-4"></div>
                            <p class="text-xs uppercase tracking-widest text-indigo-200 font-bold mb-1">Saldo Saat Ini</p>
                            <p class="text-3xl font-black text-white">{{ searchResult.saldo }}</p>
                        </div>

                        <div v-if="errorMessage" class="mt-4 p-4 bg-red-500/20 border border-red-500/30 rounded-xl text-sm text-red-100">
                            {{ errorMessage }}
                        </div>
                    </div>
                </div>

            </div>
        </main>
        <footer class="py-12 border-t border-slate-200 bg-white text-center">
            <p class="text-slate-500 text-sm">
                &copy; 2026 Tabungan Kelas - Real-time Database System.
            </p>
        </footer>
    </div>
</template>