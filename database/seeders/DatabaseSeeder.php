<?php

namespace Database\Seeders;

use App\Models\FileSystemEntry;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        $user = User::query()->create([
            'username' => 'super admin',
            'password' => bcrypt('admin'),
            'is_admin' => true
        ]);

        $fse = FileSystemEntry::query()->create([
            'group_approval_id' => null,
            'category_id' => null,
            'creator' => $user->id,
            'parent_id' => null,
            'name' => 'blah',
            'is_directory' => true,
            'due_date' => null,
            'is_approved' => null
        ]);

        FileSystemEntry::query()->create([
            'group_approval_id' => null,
            'category_id' => null,
            'creator' => $user->id,
            'parent_id' => $fse->id,
            'name' => 'blah2',
            'is_directory' => false,
            'due_date' => null,
            'is_approved' => null
        ]);

        FileSystemEntry::query()->create([
            'group_approval_id' => null,
            'category_id' => null,
            'creator' => $user->id,
            'parent_id' => $fse->id,
            'name' => 'blah3',
            'is_directory' => false,
            'due_date' => null,
            'is_approved' => null
        ]);
    }
}
