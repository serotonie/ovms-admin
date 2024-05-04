<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserInviteRequest;
use App\Models\User;
use App\Notifications\UserInvited;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

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

        $data = $query->join('model_has_roles', 'id', '=', 'model_has_roles.model_id')
            ->join('roles', 'role_id', '=', 'roles.id')
            ->select([
                'users.id',
                'users.name',
                'users.email',
                'users.created_at',
                'roles.name as role_name',
                'roles.color as role_color',
            ])
            ->paginate($request->get('limit', 10));

        return Inertia::render('Users/Index', [
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
        $user['role'] = $user->getRoleNames()->toArray()[0];

        return Inertia::render('Users/Edit', [
            'user' => $user,
            'roles' => Role::all('name', 'id')->sortBy('id')->pluck('name'),
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
            'role' => ['required', 'string', Rule::exists('roles', 'name')],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        $user->save();

        $user->syncRoles($validated['role']);

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
