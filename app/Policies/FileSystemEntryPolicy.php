<?php

namespace App\Policies;

use App\Models\FileSystemEntry;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FileSystemEntryPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\FileSystemEntry  $fileSystemEntry
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, FileSystemEntry $fileSystemEntry)
    {
        return $fileSystemEntry->permissions()->selectRaw('id,group_id,file_system_entry_id,
            , bit_or(`read`) as `read`
            ')->groupBy('file_system_entry_id')->get()->first()->read == 1;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function upload(User $user, FileSystemEntry $fileSystemEntry)
    {
         return $fileSystemEntry->permissions()->selectRaw('id,group_id,file_system_entry_id,
            , bit_or(upload) as upload
            ')->groupBy('file_system_entry_id')->get()->first()->upload == 1;
    }

    public function download(User $user, FileSystemEntry $fileSystemEntry){
        return $fileSystemEntry->permissions()->selectRaw('id,group_id,file_system_entry_id,
            , bit_or(download) as download
            ')->groupBy('file_system_entry_id')->get()->first()->download == 1;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\FileSystemEntry  $fileSystemEntry
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, FileSystemEntry $fileSystemEntry)
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\FileSystemEntry  $fileSystemEntry
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, FileSystemEntry $fileSystemEntry)
    {
        return $fileSystemEntry->permissions()->selectRaw('id,group_id,file_system_entry_id,
            , bit_or(`delete`) as `delete`
            ')->groupBy('file_system_entry_id')->get()->first()->delete == 1;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\FileSystemEntry  $fileSystemEntry
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, FileSystemEntry $fileSystemEntry)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\FileSystemEntry  $fileSystemEntry
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, FileSystemEntry $fileSystemEntry)
    {
        //
    }
}
