<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index()
    {
        $teams = Team::withCount(['users', 'orders'])
                     ->with(['users'])
                     ->orderBy('jalur')
                     ->orderBy('name')
                     ->get();

        return view('admin.teams.index', compact('teams'));
    }

    public function create()
    {
        $users = User::where('role', '!=', 'master_admin')
                     ->orderBy('name')
                     ->get();

        return view('admin.teams.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255|unique:teams',
            'jalur'      => 'required|in:digital,offset',
            'warna'      => 'required|string|max:7',
            'keterangan' => 'nullable|string',
            'user_ids'   => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $team = Team::create([
            'name'       => $validated['name'],
            'jalur'      => $validated['jalur'],
            'warna'      => $validated['warna'],
            'keterangan' => $validated['keterangan'] ?? null,
        ]);

        if (!empty($validated['user_ids'])) {
            $team->users()->sync($validated['user_ids']);
        }

        return redirect()->route('admin.teams.index')->with('success', "Tim {$team->name} berhasil dibuat.");
    }

    public function edit(Team $team)
    {
        $team->load('users');
        $users       = User::where('role', '!=', 'master_admin')->orderBy('name')->get();
        $teamUserIds = $team->users->pluck('id')->toArray();

        return view('admin.teams.edit', compact('team', 'users', 'teamUserIds'));
    }

    public function update(Request $request, Team $team)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255|unique:teams,name,' . $team->id,
            'jalur'      => 'required|in:digital,offset',
            'warna'      => 'required|string|max:7',
            'keterangan' => 'nullable|string',
            'user_ids'   => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
            'is_active'  => 'boolean',
        ]);

        $team->update([
            'name'       => $validated['name'],
            'jalur'      => $validated['jalur'],
            'warna'      => $validated['warna'],
            'keterangan' => $validated['keterangan'] ?? null,
            'is_active'  => $request->boolean('is_active', true),
        ]);

        $team->users()->sync($validated['user_ids'] ?? []);

        return redirect()->route('admin.teams.index')->with('success', "Tim {$team->name} berhasil diperbarui.");
    }

    public function destroy(Team $team)
    {
        if ($team->orders()->count() > 0) {
            return back()->with('error', 'Tim tidak bisa dihapus karena masih memiliki SPK. Nonaktifkan saja.');
        }

        $team->delete();
        return redirect()->route('admin.teams.index')->with('success', 'Tim berhasil dihapus.');
    }
}
