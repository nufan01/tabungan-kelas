<script setup>
import { ref, watch, onMounted, computed } from 'vue';
import { router, Link, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import EmptyState from '@/Components/EmptyState.vue';

const props = defineProps({
    students: {
        type: Array,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
            class: '',
        }),
    },
    allClasses: {
        type: Array,
        default: () => [],
    },
});

// Filter state
const search = ref(props.filters?.search || '');
const selectedClass = ref(props.filters?.class || '');

// Error for delete action
const deleteError = ref('');
// Success/Flash message (dari redirect)
const flashSuccess = ref('');

// --- Confirmation modal state for deleting student ---
const confirmingStudentDeletion = ref(false);
const studentIdToDelete = ref(null);

// Debounce function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Function to apply filters
function applyFilters() {
    const params = {};
    
    if (search.value) {
        params.search = search.value;
    }
    
    if (selectedClass.value) {
        params.class = selectedClass.value;
    }
    
    router.get(route('students.index'), params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

// Create debounced version of applyFilters with 300ms delay
const debouncedApplyFilters = debounce(applyFilters, 300);

// Watch for changes in search with debounce
watch(search, () => {
    debouncedApplyFilters();
});

// Watch for changes in class filter (immediate)
watch(selectedClass, () => {
    applyFilters();
});

// Modal for creating/editing student
const showModal = ref(false);
const isEditMode = ref(false);
const form = ref({
    id: null,
    nama: '',
    nis: '',
    kelas: '',
    saldo: '',
});
const errors = ref({});

function openModal() {
    isEditMode.value = false;
    form.value = { id: null, nama: '', nis: '', kelas: '', saldo: '' };
    errors.value = {};
    showModal.value = true;
}

function editStudent(student) {
    isEditMode.value = true;
    form.value = {
        id: student.id,
        nama: student.nama,
        nis: student.nis,
        kelas: student.kelas ?? '',
        saldo: student.saldo ?? '',
    };
    errors.value = {};
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    confirmingStudentDeletion.value = false;
    studentIdToDelete.value = null;
    deleteError.value = '';
}

// Untuk modal transaksi baru
function closeTransactionConfirmationModal() {
    showTransactionConfirmationModal.value = false;
    transactionConfirmationLoading.value = false;
    transactionConfirmationError.value = '';
}

// Hapus siswa
function confirmStudentDeletion(id) {
    deleteError.value = '';
    confirmingStudentDeletion.value = true;
    studentIdToDelete.value = id;
}

// Helper to extract error message (Laravel can return string or array)
function getErrorMessage(err, key = 'error') {
    if (!err || !err[key]) return null;
    const val = err[key];
    return Array.isArray(val) ? val[0] : val;
}

function submit() {
    errors.value = {};
    const payload = {
        nama: form.value.nama,
        nis: form.value.nis,
        kelas: form.value.kelas,
    };

    // Saat tambah siswa, boleh set saldo awal; saat edit, saldo tidak ikut diubah
    if (!isEditMode.value) {
        payload.saldo = form.value.saldo;
    }

    if (isEditMode.value && form.value.id) {
        router.put(route('students.update', form.value.id), payload, {
            preserveScroll: true,
            onSuccess: () => {
                closeModal();
                router.reload({ only: ['students'] });
            },
            onError: (e) => {
                errors.value = { ...e };
            },
        });
    } else {
        router.post(route('students.store'), payload, {
            preserveScroll: true,
            onSuccess: () => {
                closeModal();
                router.reload({ only: ['students'] });
            },
            onError: (e) => {
                errors.value = { ...e };
            },
        });
    }
}

function formatRupiah(value) {
    if (isNaN(value)) return "-";
    return "Rp " + Number(value).toLocaleString('id-ID', { minimumFractionDigits: 0 });
}

// --- Transaction Modal logic ---
const showTransactionModal = ref(false);
const transactionType = ref('deposit'); // 'deposit' or 'withdrawal'
const activeStudent = ref(null);
const transactionForm = ref({
    amount: '',
    description: '',
});
const transactionErrors = ref({});

// Tambahan: Modal konfirmasi transaksi
const showTransactionConfirmationModal = ref(false);
const transactionConfirmationLoading = ref(false);
const transactionConfirmationError = ref('');
// Internal state to remember callback after confirmation
let transactionConfirmedResolver = null;

// Komputasi ringkasan sebelumnya/dan estimasi saldo baru
const transactionAmountNum = computed(() => {
    const n = parseFloat(transactionForm.value.amount);
    return isNaN(n) ? 0 : n;
});
const currentStudentSaldo = computed(() => {
    if (!activeStudent.value) return 0;
    const n = parseFloat(activeStudent.value.saldo);
    return isNaN(n) ? 0 : n;
});
const estimatedNewSaldo = computed(() => {
    if (!activeStudent.value) return 0;
    if (transactionType.value === 'deposit') {
        return currentStudentSaldo.value + transactionAmountNum.value;
    } else {
        return currentStudentSaldo.value - transactionAmountNum.value;
    }
});

function openTransactionModal(student, type) {
    activeStudent.value = student;
    transactionType.value = type;
    transactionForm.value = {
        amount: '',
        description: '',
    };
    transactionErrors.value = {};
    showTransactionModal.value = true;
    showTransactionConfirmationModal.value = false;
}

function closeTransactionModal() {
    showTransactionModal.value = false;
}

// Fungsi submit dengan konfirmasi
function submitTransaction() {
    transactionErrors.value = {};

    // Validasi dasar di frontend
    if (!transactionForm.value.amount || parseFloat(transactionForm.value.amount) <= 0) {
        transactionErrors.value = { amount: 'Nominal harus lebih dari 0' };
        return;
    }

    // Tampilkan modal konfirmasi khusus transaksi
    showTransactionConfirmationModal.value = true;
    transactionConfirmationError.value = '';

    // return a promise so we can continue if user setuju
    if (transactionConfirmedResolver) transactionConfirmedResolver(false); // cancel prev
    return new Promise((resolve) => {
        transactionConfirmedResolver = resolve;
    }).then((confirmed) => {
        if (confirmed) {
            // Lanjut submit
            transactionConfirmationLoading.value = true;
            router.post(
                route('transactions.store'),
                {
                    student_id: activeStudent.value.id,
                    amount: parseFloat(transactionForm.value.amount),
                    description: transactionForm.value.description || null,
                    type: transactionType.value,
                },
                {
                    preserveScroll: true,
                    onSuccess: () => {
                        transactionConfirmationLoading.value = false;
                        closeTransactionConfirmationModal();
                        closeTransactionModal();
                        router.reload({ only: ['students'] });
                    },
                    onError: (e) => {
                        transactionConfirmationLoading.value = false;
                        // Tampilkan error pada modal transaksi, tutup modal konfirmasi
                        closeTransactionConfirmationModal();
                        const msg = getErrorMessage(e, 'error') || getErrorMessage(e, 'amount');
                        transactionErrors.value = msg ? { amount: msg } : { ...e };
                    },
                }
            );
        } else {
            // User batal
            closeTransactionConfirmationModal();
        }
        transactionConfirmedResolver = null;
    });
}

// Klik "Setuju" atau "Batal" di modal konfirmasi transaksi
function confirmTransactionAction(agree = false) {
    if (transactionConfirmedResolver) {
        transactionConfirmedResolver(agree);
    }
    // Otherwise, just close
    closeTransactionConfirmationModal();
}

// --- Delete student logic ---
function deleteStudentById(studentId) {
    deleteError.value = '';
    const student = props.students.find(std => std.id === studentId);
    if (!student) {
        deleteError.value = 'Siswa tidak ditemukan.';
        confirmingStudentDeletion.value = false;
        studentIdToDelete.value = null;
        return;
    }
    router.delete(
        route('students.destroy', student.id),
        {
            preserveScroll: true,
            onSuccess: (page) => {
                // Backend redirects with errors when saldo > 0, or with success when deleted
                const err = page?.props?.errors;
                if (err?.error) {
                    deleteError.value = getErrorMessage(err, 'error');
                    confirmingStudentDeletion.value = false;
                    studentIdToDelete.value = null;
                } else {
                    deleteError.value = '';
                    confirmingStudentDeletion.value = false;
                    studentIdToDelete.value = null;
                }
            },
            onError: (e) => {
                // Fallback: network/server error (e.g. 500)
                const msg = getErrorMessage(e, 'error') || getErrorMessage(e, 'saldo') || (e?.message);
                deleteError.value = msg || 'Gagal menghapus siswa.';
                confirmingStudentDeletion.value = false;
                studentIdToDelete.value = null;
            }
        }
    );
}

// Baca error dan flash dari redirect (setelah halaman dimuat ulang)
const page = usePage();
onMounted(() => {
    const errs = page.props.errors;
    if (errs?.error) {
        deleteError.value = getErrorMessage(errs, 'error');
    }
    const flash = page.props.flash;
    if (flash?.success) {
        flashSuccess.value = flash.success;
        setTimeout(() => { flashSuccess.value = ''; }, 5000);
    }
});
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Daftar Siswa</h2>
        </template>

        <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-bold text-gray-700">Tabel Siswa</h3>
                <button
                    @click="openModal"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:opacity-80 transition"
                >
                    Tambah Siswa
                </button>
            </div>

            <!-- Success message -->
            <div v-if="flashSuccess" class="mb-4">
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative text-sm">
                    {{ flashSuccess }}
                </div>
            </div>

            <!-- Error message for deletion -->
            <div v-if="deleteError" class="mb-4">
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative text-sm flex items-center justify-between">
                    <span>{{ deleteError }}</span>
                    <button
                        type="button"
                        @click="deleteError = ''"
                        class="ml-2 text-red-600 hover:text-red-800 font-bold"
                        aria-label="Tutup"
                    >
                        ×
                    </button>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="mb-6 bg-white p-4 rounded-lg shadow">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Search Input -->
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input
                            type="text"
                            v-model="search"
                            placeholder="Cari nama atau NIS..."
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        />
                    </div>

                    <!-- Class Filter Dropdown -->
                    <div>
                        <select
                            v-model="selectedClass"
                            class="block w-full px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        >
                            <option value="">Semua Kelas</option>
                            <option v-for="kelas in allClasses" :key="kelas" :value="kelas">
                                {{ kelas }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Table for Desktop -->
            <div v-if="students.length" class="overflow-x-auto rounded-xl shadow-sm bg-white hidden md:block">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIS</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kelas</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Saldo</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <tr v-for="student in students" :key="student.id">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <Link
                                    :href="route('students.show', student.id)"
                                    class="text-blue-600 hover:text-blue-800 hover:underline transition"
                                >
                                    {{ student.nama }}
                                </Link>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-gray-800">{{ student.nis }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-gray-800">{{ student.kelas }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-gray-800">{{ formatRupiah(student.saldo) }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    <button
                                        type="button"
                                        @click="editStudent(student)"
                                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:opacity-80 transition"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        type="button"
                                        @click="openTransactionModal(student, 'deposit')"
                                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:opacity-80 transition min-w-[72px] justify-center"
                                    >
                                        Setor
                                    </button>
                                    <button
                                        type="button"
                                        @click="openTransactionModal(student, 'withdrawal')"
                                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:opacity-80 transition min-w-[72px] justify-center"
                                    >
                                        Tarik
                                    </button>
                                    <button
                                        type="button"
                                        @click="confirmStudentDeletion(student.id)"
                                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-red-700 rounded-md hover:opacity-80 transition"
                                    >
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-else class="hidden md:block rounded-xl shadow-sm bg-white overflow-hidden">
                <EmptyState message="Data Siswa Tidak Ditemukan" />
            </div>

            <!-- Cards for Mobile View -->
            <div class="md:hidden space-y-4">
                <template v-if="students.length">
                    <div
                        v-for="student in students"
                        :key="student.id"
                        class="bg-white rounded-lg shadow p-4 flex flex-col gap-2"
                    >
                        <div class="flex items-center gap-3">
                            <div class="flex-1">
                                <Link
                                    :href="route('students.show', student.id)"
                                    class="text-base font-semibold text-blue-600 hover:text-blue-800 hover:underline transition"
                                >
                                    {{ student.nama }}
                                </Link>
                                <div class="text-xs text-gray-500">NIS: <span class="font-mono">{{ student.nis }}</span></div>
                                <div class="text-sm text-gray-600 mt-1">Kelas: <span class="font-medium text-gray-800">{{ student.kelas }}</span></div>
                            </div>
                        </div>
                        <div class="my-2">
                            <div class="font-bold text-green-700 text-lg bg-green-100 inline-block px-3 py-1 rounded">
                                {{ formatRupiah(student.saldo) }}
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2 mt-2">
                            <button
                                type="button"
                                @click="editStudent(student)"
                                class="flex-1 px-3 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:opacity-80 transition"
                            >
                                Edit
                            </button>
                            <button
                                type="button"
                                @click="openTransactionModal(student, 'deposit')"
                                class="flex-1 px-3 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:opacity-80 transition"
                            >
                                Setor
                            </button>
                            <button
                                type="button"
                                @click="openTransactionModal(student, 'withdrawal')"
                                class="flex-1 px-3 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:opacity-80 transition"
                            >
                                Tarik
                            </button>
                            <button
                                type="button"
                                @click="confirmStudentDeletion(student.id)"
                                class="flex-1 px-3 py-2 text-sm font-medium text-white bg-red-700 rounded-md hover:opacity-80 transition"
                            >
                                Hapus
                            </button>
                        </div>
                    </div>
                </template>
                <div v-else class="rounded-xl shadow-sm bg-white overflow-hidden">
                    <EmptyState message="Data Siswa Tidak Ditemukan" />
                </div>
            </div>
        </div>

        <Modal :show="showModal" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-semibold mb-4 text-gray-700">{{ isEditMode ? 'Edit Siswa' : 'Tambah Siswa' }}</h2>
                <form @submit.prevent="submit">
                    <div class="mb-4">
                        <label class="block font-medium text-gray-700 mb-1">Nama<span class="text-red-500">*</span></label>
                        <input
                            type="text"
                            v-model="form.nama"
                            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-300"
                            :class="{'border-red-500': errors.nama}"
                        />
                        <div v-if="errors.nama" class="text-sm text-red-600 mt-1">{{ errors.nama }}</div>
                    </div>

                    <div class="mb-4">
                        <label class="block font-medium text-gray-700 mb-1">NIS<span class="text-red-500">*</span></label>
                        <input
                            type="text"
                            v-model="form.nis"
                            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-300"
                            :class="{'border-red-500': errors.nis}"
                        />
                        <div v-if="errors.nis" class="text-sm text-red-600 mt-1">{{ errors.nis }}</div>
                    </div>

                    <div class="mb-4">
                        <label class="block font-medium text-gray-700 mb-1">Kelas</label>
                        <input
                            type="text"
                            v-model="form.kelas"
                            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-300"
                            :class="{'border-red-500': errors.kelas}"
                        />
                        <div v-if="errors.kelas" class="text-sm text-red-600 mt-1">{{ errors.kelas }}</div>
                    </div>

                    <div v-if="!isEditMode" class="mb-6">
                        <label class="block font-medium text-gray-700 mb-1">Saldo</label>
                        <input
                            type="number"
                            v-model="form.saldo"
                            min="0"
                            step="0.01"
                            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-300"
                            :class="{'border-red-500': errors.saldo}"
                        />
                        <div v-if="errors.saldo" class="text-sm text-red-600 mt-1">{{ errors.saldo }}</div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button
                            type="button"
                            @click="closeModal"
                            class="px-4 py-2 bg-gray-200 rounded-md text-gray-800 hover:opacity-80 transition"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md font-semibold hover:opacity-80 transition"
                        >
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- MODAL Transaksi: Form Pengisian nominal/deskripsi transaksi -->
        <Modal :show="showTransactionModal" @close="closeTransactionModal">
            <div class="p-6">
                <h2 class="text-lg font-semibold mb-4 text-gray-700">
                    {{ transactionType === 'deposit' ? 'Setor' : 'Tarik' }} — {{ activeStudent?.nama }}
                </h2>
                <form @submit.prevent="
                  () => {
                    submitTransaction();
                  }
                ">
                    <div class="mb-4">
                        <label class="block font-medium text-gray-700 mb-1">Nominal (Rp)<span class="text-red-500">*</span></label>
                        <input
                            type="number"
                            v-model="transactionForm.amount"
                            min="0"
                            step="0.01"
                            placeholder="0.00"
                            required
                            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-300"
                            :class="{'border-red-500': transactionErrors.amount || transactionErrors.error}"
                        />
                        <div v-if="transactionErrors.amount" class="text-sm text-red-600 mt-1">{{ transactionErrors.amount }}</div>
                        <div v-if="transactionErrors.error" class="text-sm text-red-600 mt-1">{{ transactionErrors.error }}</div>
                    </div>
                    <div class="mb-6">
                        <label class="block font-medium text-gray-700 mb-1">Keterangan</label>
                        <input
                            type="text"
                            v-model="transactionForm.description"
                            placeholder="Keterangan transaksi"
                            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-300"
                            :class="{'border-red-500': transactionErrors.description}"
                        />
                        <div v-if="transactionErrors.description" class="text-sm text-red-600 mt-1">{{ transactionErrors.description }}</div>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button
                            type="button"
                            @click="closeTransactionModal"
                            class="px-4 py-2 bg-gray-200 rounded-md text-gray-800 hover:opacity-80 transition"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :class="[
                                'px-4 py-2 min-w-[80px] text-white rounded-md font-semibold transition hover:opacity-80',
                                transactionType === 'deposit'
                                    ? 'bg-green-600'
                                    : 'bg-red-600'
                            ]"
                        >
                            {{ transactionType === 'deposit' ? 'Setor' : 'Tarik' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Modal Konfirmasi transaksi -->
        <Modal :show="showTransactionConfirmationModal" @close="() => confirmTransactionAction(false)">
            <div class="p-6">
                <h2 class="text-lg font-semibold mb-4 text-gray-700">Konfirmasi Transaksi</h2>
                <div class="mb-4 text-sm text-gray-800">
                    Anda akan melakukan <b>{{ transactionType === 'deposit' ? 'Setor' : 'Tarik' }}</b>
                    sebesar <b>{{ formatRupiah(transactionAmountNum) }}</b>
                    untuk siswa <b>{{ activeStudent?.nama }}</b>.
                    <br>
                    Lanjutkan?
                </div>
                <div class="mb-4 bg-gray-50 border p-3 rounded">
                    <div class="mb-1 font-medium text-gray-700">
                        Ringkasan Saldo:
                    </div>
                    <div class="flex flex-col sm:flex-row sm:gap-4 text-gray-700 text-sm">
                        <div>
                            Saldo saat ini: <b>{{ formatRupiah(currentStudentSaldo) }}</b>
                        </div>
                        <div>
                            {{ transactionType === 'deposit' ? '+ Setor' : '- Tarik' }}: <b>{{ formatRupiah(transactionAmountNum) }}</b>
                        </div>
                        <div>
                            Estimasi saldo setelah transaksi:
                            <b
                              :class="{
                                'text-green-700': estimatedNewSaldo >= 0,
                                'text-red-600': estimatedNewSaldo < 0
                              }"
                            >
                              {{ formatRupiah(estimatedNewSaldo) }}
                            </b>
                        </div>
                    </div>
                </div>
                <div v-if="transactionConfirmationError" class="mb-4 p-3 bg-red-50 border border-red-200 rounded text-red-700 text-sm">
                    {{ transactionConfirmationError }}
                </div>
                <div class="flex justify-end space-x-3 mt-4">
                    <button
                        type="button"
                        @click="confirmTransactionAction(false)"
                        class="px-4 py-2 bg-gray-200 rounded-md text-gray-800 hover:opacity-80 transition"
                        :disabled="transactionConfirmationLoading"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="confirmTransactionAction(true)"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md font-semibold hover:opacity-80 transition"
                        :disabled="transactionConfirmationLoading"
                    >
                        {{ transactionConfirmationLoading ? 'Memproses...' : 'Setuju & Lanjutkan' }}
                    </button>
                </div>
            </div>
        </Modal>

        <Modal :show="confirmingStudentDeletion" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-semibold mb-4 text-gray-700">Konfirmasi Penghapusan</h2>
                <div v-if="deleteError" class="mb-4 p-3 bg-red-50 border border-red-200 rounded text-red-700 text-sm">
                    {{ deleteError }}
                </div>
                <p class="mb-6 text-gray-600">
                    Apakah Anda yakin ingin menghapus siswa ini?
                </p>
                <div class="flex justify-end space-x-3">
                    <button
                        type="button"
                        @click="closeModal"
                        class="px-4 py-2 bg-gray-200 rounded-md text-gray-800 hover:opacity-80 transition"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="deleteStudentById(studentIdToDelete)"
                        class="px-4 py-2 bg-red-600 text-white rounded-md font-semibold hover:opacity-80 transition"
                    >
                        Hapus
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
