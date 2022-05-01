<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class Permission extends Pivot
{
    protected $fillable = ['name'];

}
