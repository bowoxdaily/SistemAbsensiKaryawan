<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class AdminProfileController extends Controller
{
    /**
     * Display the admin profile page
     */
    public function index()
    {
        // Security: Ensure only admin can access
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $user = Auth::user();

        return view('admin.profile.index', compact('user'));
    }

    /**
     * Update admin profile information
     */
    public function update(Request $request)
    {
        // Security: Ensure only admin can access
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
        ]);

        $user->update($validated);

        return redirect()
            ->route('admin.profile.index')
            ->with('success', 'Profil berhasil diperbarui');
    }

    /**
     * Update admin profile photo
     */
    public function updatePhoto(Request $request)
    {
        // Security: Ensure only admin can access
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $user = Auth::user();

        $request->validate([
            'profile_photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:10240', // Max 10MB
        ]);

        // Delete old photo if exists
        if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
            Storage::disk('public')->delete($user->profile_photo);
        }

        // Process and store new photo
        $file = $request->file('profile_photo');
        $filename = 'admin_profile_' . $user->id . '_' . time() . '.webp';

        // Create image manager instance
        $manager = new ImageManager(new Driver());

        // Read and process image
        $image = $manager->read($file);

        // Resize to max 500x500 (maintain aspect ratio)
        $image->scale(width: 500, height: 500);

        // Convert to WebP with 85% quality and save
        $path = 'profile_photos/' . $filename;
        $webpImage = $image->toWebp(quality: 85);

        Storage::disk('public')->put($path, (string) $webpImage);

        $user->update(['profile_photo' => $path]);

        return redirect()
            ->route('admin.profile.index')
            ->with('success', 'Foto profil berhasil diperbarui');
    }

    /**
     * Update admin password
     */
    public function updatePassword(Request $request)
    {
        // Security: Ensure only admin can access
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $user = Auth::user();

        $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        // Check current password
        if (!Hash::check($request->current_password, $user->password)) {
            return back()
                ->withErrors(['current_password' => 'Password lama tidak sesuai'])
                ->with('error', 'Password lama tidak sesuai');
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return redirect()
            ->route('admin.profile.index')
            ->with('success', 'Password berhasil diubah! Silakan login kembali dengan password baru.');
    }
}
