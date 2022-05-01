<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'file_system_entry_id', 'remember_on'];

    public function fileSystemEntries()
    {
        return $this->belongsTo(FileSystemEntry::class, 'file_system_entry_id', 'id');
    }
}
