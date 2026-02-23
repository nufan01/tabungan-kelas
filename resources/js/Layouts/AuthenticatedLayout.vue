<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import { Link, usePage } from '@inertiajs/vue3';

const routes = [
    {
        name: 'dashboard',
        label: 'Dashboard',
        icon: `
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M13 5v6h6" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6"/>
            </svg>
        `,
        href: () => route('dashboard'),
        isActive: () => route().current('dashboard')
    },
    {
        name: 'students.index',
        label: 'Data Siswa',
        icon: `
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6.67-5.13A4 4 0 1112 7a4 4 0 013.67 3.13z"/>
            </svg>
        `,
        href: () => route('students.index'),
        isActive: () => route().current('students.*')
    },
    {
        name: 'transactions.index',
        label: 'Transaksi',
        icon: `
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-3.3137 0-6 2.6863-6 6a6 6 0 0012 0c0-3.3137-2.6863-6-6-6zm0 0V4m0 0h4m-4 0H8"/>
            </svg>
        `,
        href: () => route('transactions.index'),
        isActive: () => route().current('transactions.index')
    }
];

const user = usePage().props.auth.user;
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-100">
            <!-- Desktop Navbar: hidden on mobile -->
            <nav class="border-b border-gray-100 bg-white hidden sm:block">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <div class="flex">
                            <!-- Logo -->
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
            <span class="text-[11px] font-black uppercase tracking-widest text-gray-800">
                Simpanan
            </span>
            <span class="text-[11px] font-black uppercase tracking-widest text-indigo-600">
                Siswa
            </span>
        </div>
    </div>
                                </Link>
                            </div>
                            <!-- Navigation Links -->
                            <div class="ms-10 flex space-x-8">
                                <NavLink
                                    v-for="r in routes"
                                    :key="r.name"
                                    :href="r.href()"
                                    :active="r.isActive()"
                                >
                                    {{ r.label }}
                                </NavLink>
                            </div>
                        </div>
                        <div class="sm:ms-6 flex items-center">
                            <!-- Settings Dropdown -->
                            <div class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none"
                                            >
                                                {{ user.name }}
                                                <svg
                                                    class="-me-0.5 ms-2 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>
                                    <template #content>
                                        <DropdownLink :href="route('profile.edit')">Profile</DropdownLink>
                                        <DropdownLink :href="route('logout')" method="post" as="button">
                                            Log Out
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>
            <!-- End Desktop Navbar -->

            <!-- Spacer for mobile bottom nav (only on mobile) -->
            <div class="block sm:hidden h-14"></div>

            <!-- Page Heading -->
            <header class="bg-white border-b border-gray-100 md:hidden">
            <div class="px-4 py-3 mx-auto">
                <div class="flex items-center justify-between">
                    
                    <div class="flex flex-col">
                        <span class="text-[10px] uppercase tracking-wider text-gray-400 font-semibold">
                            Halo, Bendahara
                        </span>
                        <h1 class="text-sm font-bold text-gray-800 leading-none">
                            {{ $page.props.auth.user.name }}
                        </h1>
                    </div>

                    <div class="relative">
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button type="button" class="flex items-center transition duration-150 ease-in-out focus:outline-none">
                                    <div class="relative">
                                        <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gradient-to-tr from-indigo-600 to-purple-500 shadow-md border-2 border-white">
                                            <span class="text-sm font-bold text-white uppercase">
                                                {{ $page.props.auth.user.name.substring(0, 2) }}
                                            </span>
                                        </div>
                                        <div class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white rounded-full"></div>
                                    </div>
                                </button>
                            </template>

                            <template #content>
                                <div class="block px-4 py-2 text-xs text-gray-400">
                                    Kelola Akun
                                </div>
                                <DropdownLink :href="route('profile.edit')"> 
                                    Pengaturan Profil 
                                </DropdownLink>
                                <DropdownLink :href="route('logout')" method="post" as="button">
                                    Keluar Aplikasi
                                </DropdownLink>
                            </template>
                        </Dropdown>
                    </div>

                </div>
            </div>
        </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
<nav class="fixed bottom-0 left-0 right-0 z-50 flex items-center justify-between px-6 py-3 bg-white/80 backdrop-blur-lg border-t border-gray-100 md:hidden">
    <Link :href="route('dashboard')" class="flex flex-col items-center gap-1 group">
        <svg :class="route().current('dashboard') ? 'text-indigo-600' : 'text-gray-400'" class="w-6 h-6 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1s1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
        </svg>
        <span :class="route().current('dashboard') ? 'text-indigo-600 font-bold' : 'text-gray-400'" class="text-[10px]">Dashboard</span>
    </Link>

    <Link :href="route('students.index')" class="flex flex-col items-center gap-1 group">
        <svg :class="route().current('students.*') ? 'text-indigo-600' : 'text-gray-400'" class="w-6 h-6 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
        <span :class="route().current('students.*') ? 'text-indigo-600 font-bold' : 'text-gray-400'" class="text-[10px]">Siswa</span>
    </Link>

    <Link :href="route('transactions.index')" class="flex flex-col items-center gap-1 group">
        <svg :class="route().current('transactions.*') ? 'text-indigo-600' : 'text-gray-400'" class="w-6 h-6 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
        </svg>
        <span :class="route().current('transactions.*') ? 'text-indigo-600 font-bold' : 'text-gray-400'" class="text-[10px]">Riwayat</span>
    </Link>
</nav>

<footer class="hidden md:block py-12 mt-auto border-t border-gray-100 bg-gray-50/30">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-center">
            
            <div class="flex items-center gap-3">
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
                </div>
                <div>
                    <h4 class="text-sm font-bold text-gray-800">Simpanan Siswa</h4>
                    <p class="text-[10px] text-gray-400 uppercase tracking-widest font-semibold">Goes to Bali</p>
                </div>
            </div>

            <div class="text-center">
                <p class="text-xs text-gray-400">
                    &copy; 2026 Aplikasi Tabungan Kelas <br>
                    Dikembangkan untuk kemudahan Bendahara.
                </p>
            </div>

            <div class="flex justify-end gap-6">
                <a href="#" class="text-xs font-medium text-gray-500 hover:text-indigo-600 transition">Bantuan</a>
                <a href="#" class="text-xs font-medium text-gray-500 hover:text-indigo-600 transition">Panduan</a>
                <a href="#" class="text-xs font-medium text-gray-500 hover:text-indigo-600 transition">Kontak</a>
            </div>
        </div>
    </div>
</footer>
            <!-- Mobile Bottom Navigation Bar: only for mobile -->
            <nav
                class="fixed bottom-0 inset-x-0 z-40 bg-white border-t border-gray-200 shadow flex justify-around items-center h-14 sm:hidden"
                style="backdrop-filter: blur(2px);"
            >
                <Link
                    v-for="r in routes"
                    :key="r.name"
                    :href="r.href()"
                    class="flex flex-col items-center justify-center flex-1 h-full hover:bg-gray-50 transition cursor-pointer"
                >
                    <span
                        v-html="r.icon"
                        :class="[
                            'transition',
                            r.isActive()
                                ? 'text-indigo-600'
                                : 'text-gray-400'
                        ]"
                    />
                    <span
                        :class="[
                            'text-xs',
                            r.isActive()
                                ? 'text-indigo-700 font-medium'
                                : 'text-gray-500'
                        ]"
                    >{{ r.label }}</span>
                </Link>
                <!-- Mobile Akun Dropdown -->
                <Dropdown align="top" width="44">
                    <template #trigger>
                    </template>
                    <template #content>
                        <div class="px-4 py-2 text-black border-b">
                            <div class="font-medium">{{ user.name }}</div>
                            <div class="text-xs text-gray-500">{{ user.email }}</div>
                        </div>
                        <DropdownLink :href="route('profile.edit')">Profile</DropdownLink>
                        <DropdownLink :href="route('logout')" method="post" as="button">
                            Log Out
                        </DropdownLink>
                    </template>
                </Dropdown>
            </nav>
        </div>
    </div>
    
</template>
