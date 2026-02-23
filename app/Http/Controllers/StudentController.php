<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $class = $request->input('class');

        $query = Student::query();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('nis', 'like', '%' . $search . '%');
            });
        }

        if ($class) {
            $query->where('kelas', $class);
        }

        $students = $query->get();
        
        // Get all unique classes for filter dropdown
        $allClasses = Student::whereNotNull('kelas')
            ->where('kelas', '!=', '')
            ->distinct()
            ->pluck('kelas')
            ->sort()
            ->values();

        return Inertia::render('Students/Index', [
            'students' => $students,
            'filters' => [
                'search' => $search,
                'class' => $class,
            ],
            'allClasses' => $allClasses,
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
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'nis' => ['required', 'string', 'max:255', 'unique:students,nis'],
            'kelas' => ['nullable', 'string', 'max:255'],
            'saldo' => ['nullable', 'numeric', 'min:0'],
        ]);

        Student::create($validated);

        return redirect()->route('students.index')
            ->with('success', 'Siswa berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Student $student): Response
    {
        $student->load(['transactions' => fn ($query) => $query->latest()]);

        return Inertia::render('Students/Show', [
            'student' => $student,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Student $student)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'nis' => ['required', 'string', 'max:255', Rule::unique('students', 'nis')->ignore($student->id)],
            'kelas' => ['nullable', 'string', 'max:255'],
        ]);

        $student->update($validated);

        return redirect()->route('students.index')
            ->with('success', 'Siswa berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Student $student): RedirectResponse
    {
        $saldo = (float) $student->saldo;
        if ($saldo > 0) {
            return redirect()->back()
                ->withErrors(['error' => 'Siswa masih memiliki saldo (Rp ' . number_format($saldo, 0, ',', '.') . '). Tidak dapat dihapus. Tarik saldo terlebih dahulu.']);
        }

        $student->delete();

        return redirect()->back()
            ->with('success', 'Siswa berhasil dihapus.');
    }
}
