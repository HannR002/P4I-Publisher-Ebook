<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookLicense;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()
            ->withCount(['orders as completed_orders_count' => fn($q) => $q->where('status', 'success')])
            ->withCount(['bookLicenses as active_licenses_count' => fn($q) => $q->where('status', 'active')])
            ->withSum(['orders as total_spent' => fn($q) => $q->where('status', 'success')], 'gross_amount');

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('email', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'suspended') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('role') && $request->role !== 'all') {
            if ($request->role === 'admin') {
                $query->where('is_admin', true);
            } elseif ($request->role === 'customer') {
                $query->where('is_admin', false)->where('is_author', false);
            } elseif ($request->role === 'author') {
                $query->where('is_author', true);
            }
        }

        $users = $query->latest()->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user)
    {
        $user->load([
            'orders' => fn($q) => $q->with('items.book')->latest(),
            'bookLicenses' => fn($q) => $q->with('book')->latest(),
        ]);
        
        $user->loadCount(['orders as completed_orders_count' => fn($q) => $q->where('status', 'success')]);
        $user->loadCount(['bookLicenses as active_licenses_count' => fn($q) => $q->where('status', 'active')]);
        $user->loadSum(['orders as total_spent' => fn($q) => $q->where('status', 'success')], 'gross_amount');

        return view('admin.users.show', compact('user'));
    }

    public function toggleStatus(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'Aksi ditolak: Anda tidak dapat menangguhkan akun Anda sendiri.']);
        }

        $user->update(['is_active' => !$user->is_active]);

        Log::warning('[SECURITY] User status changed by admin', [
            'admin_id' => auth()->id(),
            'target_user_id' => $user->id,
            'new_status' => $user->is_active ? 'active' : 'suspended'
        ]);

        return back()->with('success', 'Status pengguna berhasil diperbarui.');
    }

    public function revokeLicense(Request $request, User $user, BookLicense $license)
    {
        if ($license->user_id !== $user->id) {
            abort(404);
        }

        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $license->update([
            'status' => 'revoked',
            'revocation_reason' => $request->reason
        ]);

        Log::warning('[SECURITY] Book license revoked', [
            'admin_id' => auth()->id(),
            'license_id' => $license->id,
            'user_id' => $user->id,
            'reason' => $request->reason
        ]);

        return back()->with('success', 'Lisensi buku berhasil dicabut.');
    }

    public function restoreLicense(User $user, BookLicense $license)
    {
        if ($license->user_id !== $user->id) {
            abort(404);
        }

        $license->update([
            'status' => 'active',
            'revocation_reason' => null
        ]);

        Log::info('[SECURITY] Book license restored', [
            'admin_id' => auth()->id(),
            'license_id' => $license->id,
            'user_id' => $user->id
        ]);

        return back()->with('success', 'Lisensi buku berhasil dipulihkan.');
    }
}
