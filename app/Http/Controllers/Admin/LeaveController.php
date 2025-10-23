<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LeaveController extends Controller
{
    /**
     * Display list of leave requests
     */
    public function index(Request $request)
    {
        // Security: Ensure only admin can access
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $query = Leave::with(['employee.department', 'employee.position'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Filter by leave type
        if ($request->has('leave_type') && $request->leave_type != '') {
            $query->where('leave_type', $request->leave_type);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->start_date != '') {
            $query->whereDate('start_date', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date != '') {
            $query->whereDate('end_date', '<=', $request->end_date);
        }

        $leaves = $query->paginate(20);

        // Statistics
        $stats = [
            'pending' => Leave::where('status', 'pending')->count(),
            'approved' => Leave::where('status', 'approved')->count(),
            'rejected' => Leave::where('status', 'rejected')->count(),
            'total' => Leave::count(),
        ];

        return view('admin.leave.index', compact('leaves', 'stats'));
    }

    /**
     * Show detail of leave request
     */
    public function show($id)
    {
        // Security check
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $leave = Leave::with(['employee.department', 'employee.position'])->findOrFail($id);

        return view('admin.leave.show', compact('leave'));
    }

    /**
     * Approve leave request
     */
    public function approve($id)
    {
        // Security check
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $leave = Leave::findOrFail($id);

        if ($leave->status !== 'pending') {
            return back()->with('error', 'Hanya pengajuan dengan status pending yang dapat disetujui');
        }

        $leave->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return redirect()
            ->route('admin.leave.index')
            ->with('success', 'Pengajuan cuti berhasil disetujui');
    }

    /**
     * Reject leave request
     */
    public function reject(Request $request, $id)
    {
        // Security check
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $leave = Leave::findOrFail($id);

        if ($leave->status !== 'pending') {
            return back()->with('error', 'Hanya pengajuan dengan status pending yang dapat ditolak');
        }

        $leave->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return redirect()
            ->route('admin.leave.index')
            ->with('success', 'Pengajuan cuti berhasil ditolak');
    }

    /**
     * Delete leave request (admin only)
     */
    public function destroy($id)
    {
        // Security check
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $leave = Leave::findOrFail($id);

        // Delete attachment if exists
        if ($leave->attachment && Storage::disk('public')->exists($leave->attachment)) {
            Storage::disk('public')->delete($leave->attachment);
        }

        $leave->delete();

        return redirect()
            ->route('admin.leave.index')
            ->with('success', 'Data cuti berhasil dihapus');
    }
}
