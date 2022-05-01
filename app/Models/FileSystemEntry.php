<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

class FileSystemEntry extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, HasRecursiveRelationships;

    protected $fillable = ['group_approval_id', 'category_id', 'creator', 'parent_id', 'name', 'is_directory', 'due_date', 'is_approved'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator', 'id');
    }

    public function remindedUsers()
    {
        return $this->belongsToMany(User::class, 'reminders', 'file_system_entry_id', 'user_id');
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'fileSystemEntry_group',  'file_system_entry_id', 'group_id');
    }

    public function permissions(){
        return $this->hasOne('fileSystemEntry_group')->selectRaw(
            'select bit_or(fileSystemEntry_group.read) as read,
            bit_or(fileSystemEntry_group.upload) as upload,
            bit_or(fileSystemEntry_group.download) as download,
            bit_or(fileSystemEntry_group.delete) as del
            from groups,users,group_user,fileSystemEntry_group,file_system_entries where
            groups.id = group_user.group_id and users.id = group_user.user_id and groups.id = fileSystemEntry_group.group_id
            and file_system_entries.id = fileSystemEntry_group.file_system_entry_id and users.id = '. auth()->user()->id
        );
    }
}
