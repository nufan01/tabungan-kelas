<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        // Menghitung total kas (Penerimaan - Pengeluaran)
        'totalKas' => \App\Models\Transaction::where('type', 'deposit')->sum('amount') - 
                      \App\Models\Transaction::where('type', 'withdrawal')->sum('amount'),
        'totalSiswa' => \App\Models\Student::count(),
        'topStudents' => \App\Models\Student::take(3)->get(), // Dummy sementara
    ]);
});
*/

// Halaman Utama
Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

// API untuk Cek Saldo (Dijalankan via Axios/Inertia)
Route::post('/cek-saldo', [WelcomeController::class, 'cekSaldo'])->name('cek.saldo');



Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    Route::resource('students', StudentController::class);
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
});

require __DIR__.'/auth.php';
