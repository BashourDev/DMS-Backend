<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

class Category extends Model
{
    use HasFactory, HasRecursiveRelationships;

    protected $fillable = ['parent_id', 'name'];

    public function getCustomPaths()
    {
        return [
            [
                'name' => 'custom_path',
                'column' => 'name',
                'separator' => ' / ',
            ],
        ];
    }

    public function fileSystemEntries()
    {
        return $this->hasMany(FileSystemEntry::class, 'category_id', 'id');
    }
}
