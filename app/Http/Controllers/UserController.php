<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index(Request $request)
    {
        return response(User::query()->where('username', 'like', '%'.$request->get('search').'%')->orderByDesc('updated_at')->get());
    }

    public function show(User $user)
    {
        return response($user);
    }

    public function create(Request $request)
    {
        $request->validate([
            'username' => 'unique:users,username',
        ]);

        $user = User::query()->create([
            'username' => $request->get('username'),
            'password' => bcrypt($request->get('password')),
            'is_admin' => $request->get('is_admin')
        ]);

        return response($user);
    }

    public function update(Request $request, User $user)
    {
        $v = Validator::make($request->only(['username']), [
            'username' => [
                'required',
                Rule::unique('users')->ignore($user->id),
            ],
        ]);

        try {
            $v->validate();
        } catch (ValidationException $e) {
        }

        $user->update([
            'username' => $request->get('username'),
            'password' => bcrypt($request->get('password')),
            'is_admin' => $request->get('is_admin')
        ]);

        return response('ok');
    }

    public function destroy(User $user)
    {
        return response($user->delete());
    }

    public function userGroups(User $user)
    {
        return response($user->groups()->get());
    }

    public function userAvailableGroups(User $user)
    {
        $groups = Group::query()->whereDoesntHave('users', function ($query) use ($user) {
            $query->where('users.id', '=', $user->id);
        })->get();
        return response($groups);
    }
}
