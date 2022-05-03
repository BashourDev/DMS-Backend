<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
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

    public function permissions()
    {
        return $this->hasMany(FileSystemEntryGroup::class, 'file_system_entry_id','id')
            ->whereHas('groups', function (Builder $query) {
            $query->whereHas('groupUser', function (Builder $query){
                $query->where('group_user.user_id',auth()->user()->id);
            });
        });
    }
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function group_approval()
    {
        return $this->belongsTo(Group::class, 'group_approval_id', 'id');
    }
}
