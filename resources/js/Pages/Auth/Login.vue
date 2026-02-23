<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-slate-50">
        <Head title="Log in Bendahara" />

        <div class="w-full sm:max-w-md mt-6 px-8 py-10 bg-white shadow-[0_20px_50px_rgba(79,70,229,0.1)] overflow-hidden sm:rounded-3xl border border-slate-100">
            <div class="mb-10 text-center">
                <Link href="/" class="flex justify-center mb-6">
                    <div class="p-3 bg-indigo-600 rounded-2xl shadow-lg shadow-indigo-200">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                </Link>
                <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Akses Bendahara</h2>
                <p class="text-slate-500 text-sm mt-2">Silakan masuk untuk mengelola tabungan siswa.</p>
            </div>

            <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
                {{ status }}
            </div>

            <form @submit.prevent="submit">
                <div class="space-y-1">
                    <InputLabel for="email" value="Email Institusi" class="text-slate-700 font-semibold" />
                    <TextInput
                        id="email"
                        type="email"
                        class="mt-1 block w-full border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm transition"
                        v-model="form.email"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="nama@sekolah.id"
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div class="mt-6 space-y-1">
                    <div class="flex justify-between items-center">
                        <InputLabel for="password" value="Kata Sandi" class="text-slate-700 font-semibold" />
                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="text-xs font-medium text-indigo-600 hover:text-indigo-500 transition"
                        >
                            Lupa sandi?
                        </Link>
                    </div>
                    <TextInput
                        id="password"
                        type="password"
                        class="mt-1 block w-full border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-sm transition"
                        v-model="form.password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                    />
                    <InputError class="mt-2" :message="form.errors.password" />
                </div>

                <div class="block mt-6">
                    <label class="flex items-center cursor-pointer group">
                        <Checkbox name="remember" v-model:checked="form.remember" class="rounded text-indigo-600 focus:ring-indigo-500 border-slate-300" />
                        <span class="ms-2 text-sm text-slate-600 group-hover:text-slate-900 transition">Tetap masuk di perangkat ini</span>
                    </label>
                </div>

                <div class="mt-8">
                    <PrimaryButton
                        class="w-full justify-center py-4 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 focus:bg-indigo-700 text-sm font-bold tracking-wide rounded-xl shadow-lg shadow-indigo-100 transition-all transform active:scale-[0.98]"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        Masuk ke Dashboard
                    </PrimaryButton>
                </div>

                <div class="mt-8 text-center">
                    <Link href="/" class="text-sm text-slate-400 hover:text-indigo-600 transition flex items-center justify-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Kembali ke Beranda
                    </Link>
                </div>
            </form>
        </div>
        
        <p class="mt-8 text-slate-400 text-xs tracking-widest uppercase font-medium">
            &copy; 2026 Celengan Toga Security
        </p>
    </div>
</template>

<style scoped>
/* Menghapus default focus ring yang kaku */
input:focus {
    outline: none;
}
</style>