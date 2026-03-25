<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('teams')->where('id', '!=', auth()->id());

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        if ($request->role) {
            $query->where('role', $request->role);
        }

        if ($request->team_id) {
            $query->whereHas('teams', fn($q) => $q->where('teams.id', $request->team_id));
        }

        $users = $query->orderBy('name')->paginate(20)->withQueryString();
        $teams = Team::where('is_active', true)->get();

        return view('admin.users.index', compact('users', 'teams'));
    }

    public function create()
    {
        $teams = Team::where('is_active', true)->get();
        return view('admin.users.create', compact('teams'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => ['required', Password::min(8)],
            'role'     => 'required|in:master_admin,ppic,koor',
            'team_ids' => 'nullable|array',
            'team_ids.*' => 'exists:teams,id',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => $validated['role'],
        ]);

        // Assign ke tim (master_admin tidak perlu tim)
        if ($validated['role'] !== 'master_admin' && !empty($validated['team_ids'])) {
            $user->teams()->sync($validated['team_ids']);
        }

        return redirect()->route('admin.users.index')->with('success', "Akun {$user->name} berhasil dibuat.");
    }

    public function edit(User $user)
    {
        $teams        = Team::where('is_active', true)->get();
        $userTeamIds  = $user->teams->pluck('id')->toArray();
        return view('admin.users.edit', compact('user', 'teams', 'userTeamIds'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $user->id,
            'password' => ['nullable', Password::min(8)],
            'role'     => 'required|in:master_admin,ppic,koor',
            'team_ids' => 'nullable|array',
            'team_ids.*' => 'exists:teams,id',
            'is_active' => 'boolean',
        ]);

        $data = [
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'role'      => $validated['role'],
            'is_active' => $request->boolean('is_active', true),
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        // Update tim
        if ($validated['role'] === 'master_admin') {
            $user->teams()->detach();
        } else {
            $user->teams()->sync($validated['team_ids'] ?? []);
        }

        return redirect()->route('admin.users.index')->with('success', "Akun {$user->name} berhasil diperbarui.");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak bisa menghapus akun sendiri.');
        }

        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Akun berhasil dihapus.');
    }
}
