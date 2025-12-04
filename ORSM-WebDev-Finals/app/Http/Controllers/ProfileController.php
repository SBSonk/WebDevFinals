<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Services\ActivityLogger;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Capture originals for audit comparison
        $originalName = $user->name;
        $originalEmail = $user->email;

        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        // Audit: name change
        if ($originalName !== $user->name) {
            ActivityLogger::log('name_changed', 'User', $user->id, [
                'field' => 'name',
                'old_value' => $originalName,
                'new_value' => $user->name,
            ]);
        }

        // Audit: email change
        if ($originalEmail !== $user->email) {
            ActivityLogger::log('email_changed', 'User', $user->id, [
                'field' => 'email',
                'old_value' => $originalEmail,
                'new_value' => $user->email,
            ]);
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Audit: account deletion (self-initiated)
        ActivityLogger::log('account_deleted', 'User', $user->id, [
            'by' => 'self',
            'email' => $user->email,
        ]);

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
