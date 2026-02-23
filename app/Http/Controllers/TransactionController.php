<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $transactions = Transaction::with('student')->latest()->get();

        return \Inertia\Inertia::render('Transactions/Index', [
            'transactions' => $transactions,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * Aturan tambahan:
     *  - Jika withdrawal (tarik), nominal tidak boleh melebihi saldo siswa.
     *  - Nominal minimal 1.
     *  - Semua update saldo & catatan transaksi harus atomic dengan DB transaction.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'type' => ['required', Rule::in(['deposit', 'withdrawal'])],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        DB::beginTransaction();
        try {
            // Lock student row for update (race condition safe)
            $student = Student::lockForUpdate()->findOrFail($validated['student_id']);

            // Jika withdrawal: pastikan saldo cukup
            if ($validated['type'] === 'withdrawal') {
                if ($validated['amount'] > $student->saldo) {
                    DB::rollBack();
                    throw ValidationException::withMessages([
                        'amount' => 'Saldo tidak mencukupi untuk melakukan penarikan.',
                    ]);
                }
                $student->saldo = (float)$student->saldo - (float)$validated['amount'];
            } else {
                // Deposit
                $student->saldo = (float)$student->saldo + (float)$validated['amount'];
            }

            $student->save();
            Transaction::create($validated);

            DB::commit();
            return redirect()->back()->with('success', 'Transaksi berhasil dicatat.');
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->withErrors(['amount' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Transaction $transaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaction $transaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        //
    }
}
