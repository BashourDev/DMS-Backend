<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class Group extends Pivot
{
    protected $fillable = ['name'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'group_user', 'group_id', 'user_id');
    }



    public function fileSystemEntries()
    {
        return $this->belongsToMany(FileSystemEntry::class, 'fileSystemEntry_group', 'group_id', 'file_system_entry_id');
    }

    public function groupUser(){
        return $this->hasMany(GroupUser::class,'group_id', 'id' );
    }

}
