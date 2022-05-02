<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileSystemEntryGroup extends Model
{
    use HasFactory;
    protected $table='fileSystemEntry_group';
    public function groups(){
        return $this->hasOne(Group::class,'id','group_id');
    }

}
