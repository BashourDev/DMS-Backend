<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'file_system_entry_id'];

    public function fileSystemEntries()
    {
        return $this->hasOne(FileSystemEntry::class, 'id', 'file_system_entry_id');
    }

    public function user(){
        return $this->hasOne(User::class, 'id','user_id');
    }
}
