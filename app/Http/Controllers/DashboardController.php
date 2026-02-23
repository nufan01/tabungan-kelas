<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // Total balance: Sum dari semua saldo di tabel students
        $totalBalance = Student::sum('saldo');

        // Total students: Count dari semua siswa
        $totalStudents = Student::count();

        // Today transactions: Count transaksi yang dibuat hari ini
        $todayTransactions = Transaction::whereDate('created_at', today())->count();

        // Recent transactions: 5 transaksi terakhir beserta data siswanya
        $recentTransactions = Transaction::with('student')
            ->latest()
            ->take(5)
            ->get();

        return Inertia::render('Dashboard', [
            'totalBalance' => $totalBalance,
            'totalStudents' => $totalStudents,
            'todayTransactions' => $todayTransactions,
            'recentTransactions' => $recentTransactions,
        ]);
    }
}
