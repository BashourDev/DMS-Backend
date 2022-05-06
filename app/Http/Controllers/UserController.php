<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
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
        return response($user->groups);
    }

    public function userAvailableGroups(User $user)
    {
        $groups = Group::query()->whereDoesntHave('users', function ($query) use ($user) {
            $query->where('users.id', '=', $user->id);
        })->get();
        return response($groups);
    }

    public function myReminders(){
        return response(['documents' => auth()->user()->FSEreminders()->with('permissions',function ( $query){
            $query->selectRaw('id,group_id,file_system_entry_id,
            bit_or(`read`) as `read`
            , bit_or(upload) as upload
            , bit_or(download) as download
            , bit_or(`delete`) as `delete`')->groupBy('file_system_entry_id');
        })->with('media:id,model_type,model_id,disk,file_name')->whereRelation('permissions','read',1 )
            ->orderByDesc('file_system_entries.due_date')->get()]);
    }

    public function remindersCount(){
        return response(auth()->user()->loadCount('FSEreminders'));
    }

}
