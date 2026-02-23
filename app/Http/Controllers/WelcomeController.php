<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WelcomeController extends Controller
{
    public function index()
    {
        // Hitung total kas bersih dari seluruh transaksi yang ada
        $totalBalance = Student::sum('saldo');
        $totalMasuk = Transaction::where('type', 'deposit')->sum('amount');
        $totalKeluar = Transaction::where('type', 'withdrawal')->sum('amount');
        $saldoTotal = $totalMasuk - $totalKeluar;

        return Inertia::render('Welcome', [
            'totalBalance' => $totalBalance,
            'canLogin' => true,
            'totalKas' => (float) $saldoTotal, // Pastikan angka murni untuk diproses toLocaleString di Vue
            'totalSiswa' => Student::count(),
            // Ambil 3 penabung dengan saldo terbanyak untuk tampilan
            'topStudents' => Student::orderBy('saldo', 'desc')->take(3)->get(),
        ]);
    }

    public function cekSaldo(Request $request)
    {
        $request->validate([
            'identifier' => 'required'
        ]);

        // Cari siswa berdasarkan NIS
        $siswa = Student::where('nis', $request->identifier)->first();

        if (!$siswa) {
            return response()->json(['message' => 'Siswa tidak ditemukan'], 404);
        }

        // Langsung ambil kolom saldo karena sistem Anda sudah mengupdate kolom ini secara otomatis
        return response()->json([
            'nama' => $siswa->nama,
            'saldo' => "Rp " . number_format($siswa->saldo, 0, ',', '.')
        ]);
    }
}