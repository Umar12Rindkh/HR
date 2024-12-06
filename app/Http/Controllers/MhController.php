<?php

namespace App\Http\Controllers;

use PDF;
use App\Models\Kpi;
use App\Models\User;
use App\Models\Harian;
use App\Models\Mingguan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;

class MhController extends Controller
{
    public function index()
    {
        return view('mh.dashboard');
    }

    public function adduser()
    {
        $users = User::where('role', 'hrd')->get();
        return view('mh.adduser.index', compact('users'));
    }

    public function deleteuser($id)
    {
        $user = User::findOrFail($id);
        $user->delete(); // Delete the user

        return redirect()->route('add.user')->with('success', 'User deleted successfully!');
    }

    public function doadduser(Request $request)
    {
        // Validasi input datadoregister
        $request->validate([
            'name' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Create the user with correct field mappings
        User::create([
            'name' => $request->name,
            'jabatan' => $request->jabatan, // Corrected this line
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('add.user')->with('success', 'Berhasil Menambahkan Karyawan');
    }

    public function print()
    {
        return view('mh.print.index');
    }

    public function viewkpi(Request $request)
    {
        $searchName = $request->input('search');
        $bulan = $request->input('bulan');
        $tahun = $request->input('tahun');

        $allKpis = Kpi::with('users')->get();

        $filteredKpis = Kpi::with('users')
            ->when($searchName, function ($query, $searchName) {
                $query->whereHas('users', function ($query) use ($searchName) {
                    $query->where('name', $searchName);
                });
            })
            ->when($bulan, function ($query, $bulan) {
                $monthNumber = $this->getMonthNumber($bulan);
                $query->whereMonth('created_at', $monthNumber);
            })
            ->when($tahun, function ($query, $tahun) {
                $query->whereYear('created_at', $tahun);
            })
            ->get();
        $jabatan = null;
        if ($searchName && $filteredKpis->isNotEmpty()) {
            $jabatan = $filteredKpis->first()->users->jabatan;
        }

        $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        
        return view('mh.kpi.index', compact('allKpis', 'filteredKpis', 'months', 'jabatan'));
    }

    private function getMonthNumber($monthName)
    {
        $months = [
            'Januari' => 1,
            'Februari' => 2,
            'Maret' => 3,
            'April' => 4,
            'Mei' => 5,
            'Juni' => 6,
            'Juli' => 7,
            'Agustus' => 8,
            'September' => 9,
            'Oktober' => 10,
            'November' => 11,
            'Desember' => 12,
        ];
        return $months[$monthName] ?? null;
    }

    public function addappraisal()
    {
        return view('mh.appraisal.appraisal');
    }

    public function appraisal()
    {
        return view('mh.appraisal.appraisal');
    }

    public function indexappraisal()
    {
        return view('mh.appraisal.index');
    }

    public function kpi()
    {
        // Fetch all users with role 'marketing' and include 'name' and 'jabatan'
        $users = User::where('role', 'hrd')->select('name', 'jabatan')->get();

        // Pass users to the view
        return view('mh.kpi.addkpi', compact('users'));
    }

    public function add_kpi(Request $request)
    {
        // Validate inputs
        $request->validate([
            'nama' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    if (!User::where('name', $value)->exists()) {
                        $fail('The selected name does not exist in the users table.');
                    }
                },
            ],
            'jabatan' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($request) {
                    if (
                        !User::where('name', $request->nama)
                            ->where('jabatan', $value)
                            ->exists()
                    ) {
                        $fail('The provided position does not match the user.');
                    }
                },
            ],
            'desc' => 'required|string|max:255',
            'bobot' => 'required|numeric|min:0|max:100',
            'target' => 'required|numeric|min:0',
            'realisasi' => 'required|numeric|min:0',
            'month' => 'required|string|min:0',
            'year' => 'required|numeric|min:1900|max:' . date('Y'),
        ]);

        // Calculate scores
        $skor = ($request->realisasi / $request->target) * 100;
        $finalSkor = ($skor * $request->bobot) / 100;

        // Save KPI record
        Kpi::create([
            'nama' => $request->nama,
            'jabatan' => $request->jabatan,
            'desc' => $request->desc,
            'bobot' => $request->bobot,
            'target' => $request->target,
            'realisasi' => $request->realisasi,
            'skor' => $skor,
            'final_skor' => $finalSkor,
            'month' => $request->month,
            'year' => $request->year,
        ]);

        // Redirect to KPI page with success message
        return redirect()->route('kpi')->with('success', 'KPI added successfully!');
    }

    public function kpiedit($id)
    {
        $kpi = Kpi::find($id);
        return view('mh.kpi.edit', compact('kpi'));
    }

    public function kpiupdate(Request $request, $id)
    {
        $request->validate([
            'desc' => 'required|string|max:255', // Validate desc
            'bobot' => 'required|numeric', // Validate bobot (decimal)
            'target' => 'required|numeric', // Validate target (decimal)
            'realisasi' => 'required|numeric', // Validate realisasi (decimal)
        ]);


        $kpi = Kpi::find($id);
        $kpi->update([
            'desc' => $request->desc, // Insert desc
            'bobot' => $request->bobot, // Insert bobot
            'target' => $request->target, // Insert target
            'realisasi' => $request->realisasi, // Insert realisasi
        ]);

        return redirect()->route('kpi')->with('success', 'KPI updated successfully!');
    }
    public function kpidestroy($id)
    {
        Kpi::destroy($id);
        return redirect()->route('kpi')->with('success', 'KPI deleted successfully!');
    }
}
