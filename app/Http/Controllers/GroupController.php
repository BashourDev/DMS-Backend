<?php

namespace App\Http\Controllers;

use App\Models\FileSystemEntry;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return response(Group::query()->where('name', 'like', '%'.$request->get('search').'%')->orderByDesc('updated_at')->get());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $group = Group::query()->create([
            'name' => $request->get('name')
        ]);

        return response($group);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\Response
     */
    public function show(Group $group)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Group $group)
    {
        $group->name = $request->get('name');
        $group->save();

        return response($group);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\Response
     */
    public function destroy(Group $group)
    {
        return response($group->delete());
    }

    public function removeUser(Request $request, User $user, Group $group)
    {
        return response($group->users()->detach($user->id));
    }

    public function givePermissionToFileSystem(Request $request, Group $group, FileSystemEntry $fileSystemEntry)
    {
        return response($group->fileSystemEntries()->attach($request->get('permission')));
    }

    public function addUser(Request $request, User $user, Group $group)
    {
        return response($group->users()->attach($user->id));
    }

    public function groupUsers(Group $group)
    {
        return response($group->users()->get());
    }

    public function groupAvailableUsers(Group $group)
    {
        $users = User::query()->whereDoesntHave('groups', function ($query) use ($group) {
            $query->where('group.id', '=', $group->id);
        })->get();
        return response($users);
    }

    public function linkFileSystemEntry(Request $request, Group $group, FileSystemEntry $fileSystemEntry)
    {
        $group->fileSystemEntries()->attach(
            $fileSystemEntry->id,
            ['read'=>$request->get('read'),
            'upload'=>$request->get('upload'),
            'download'=>$request->get('download'),
            'delete'=>$request->get('delete')]);
        return response(['status'=>'file system entry linked!']);
    }

    public function updateFileSystemEntryPermissions(Request $request, Group $group, FileSystemEntry $fileSystemEntry)
    {
        $group->fileSystemEntries()->updateExistingPivot(
            $fileSystemEntry->id,
            ['read'=>$request->get('read'),
            'upload'=>$request->get('upload'),
            'download'=>$request->get('download'),
            'delete'=>$request->get('delete')]);
        return response(['status'=>'file system entry permissions updated!']);
    }
}
