<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserInviteRequest;
use App\Models\User;
use App\Notifications\UserInvited;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class AdminUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::query()->when($request->get('search'), function ($query, $search) {
            $search = strtolower(trim($search));

            return $query->whereRaw('LOWER(users.name) LIKE ?', ["%$search%"]);
        })->when($request->get('sort'), function ($query, $sortBy) {
            return $query->orderBy($sortBy['key'], $sortBy['order']);
        });

        $data = $query
            ->select([
                'users.id',
                'users.name',
                'users.email',
                'users.role',
                'users.created_at',
            ])
            ->paginate($request->get('limit', 10));

        return Inertia::render('Admin/Users/Index', [
            'data' => $data,
        ]);
    }

    /**
     * Send the invitation.
     */
    public function invite(StoreUserInviteRequest $request)
    {
        $validated = $request->validated();
        $email = $validated['email'];
        $role = $validated['role'];

        try {
            Notification::route('mail', $email)->notify(new UserInvited($role, $request->user()));
        } catch (\Exception $e) {
            return redirect(route('admin.users.index'))->with('error', $e->getMessage());
        }

        return redirect(route('admin.users.index'))->with('success', "An invite with role $role has been sent to $email.");

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'role' => ['required', 'string', Rule::in(['admin', 'user'])],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];

        $user->save();

        return redirect(route('admin.users.index'))->with('success', "$user->name has been updated");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();
    }
}
