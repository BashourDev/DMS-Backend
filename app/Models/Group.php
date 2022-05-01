<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class Group extends Pivot
{
    protected $fillable = ['name'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'group_user', 'user_id', 'group_id');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'group_permission');
    }

    public function fileSystemEntries()
    {
        return $this->belongsToMany(FileSystemEntry::class, 'group_permission', 'group_id', 'file_system_entry_id');
    }

}
