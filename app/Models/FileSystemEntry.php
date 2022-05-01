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
            'SELECT
                            BIT_OR(`fileSystemEntry_group`.`read`) AS `read`,
                            BIT_OR(fileSystemEntry_group.upload) AS upload,
                            BIT_OR(fileSystemEntry_group.download) AS download,
                            BIT_OR(fileSystemEntry_group.`delete`) AS `delete`
                        FROM
                            `group`,
                            users,
                            group_user,
                            fileSystemEntry_group,
                            file_system_entries
                        WHERE
                            `group`.id = group_user.group_id
                                AND users.id = group_user.user_id
                                AND `group`.id = fileSystemEntry_group.group_id
                                AND file_system_entries.id = fileSystemEntry_group.file_system_entry_id
                                AND users.id = '. auth()->user()->id
        );
    }
}
