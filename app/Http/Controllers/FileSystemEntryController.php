<?php

namespace App\Http\Controllers;

use App\Models\FileSystemEntry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use function Symfony\Component\Mime\Header\get;

class FileSystemEntryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, FileSystemEntry $fileSystemEntry)
    {

        return response(['documents' => $fileSystemEntry->children()->with('permissions',function ( $query){
            $query->selectRaw('id,group_id,file_system_entry_id,
            bit_or(`read`) as `read`
            , bit_or(upload) as upload
            , bit_or(download) as download
            , bit_or(`delete`) as `delete`');
//                ->groupBy(['id','group_id','file_system_entry_id']);
        })->whereRelation('permissions', function ($query){
                $query->selectRaw('id,group_id,file_system_entry_id,bit_or(`read`) as `read`')->having(' `read` <> 0');
            })->orderBy('name')->get(), 'parent' => $fileSystemEntry->id]);
//        return response(['documents' => $fileSystemEntry->children()->orderBy('name')->with('permissions')->whereRelation('permissions', 'read',true)->get(), 'parent' => $fileSystemEntry->id]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param FileSystemEntry $parent
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function store(Request $request, $parent)
    {
        $fse = FileSystemEntry::query()->create([
            'group_approval_id' => $request->get('group_approval_id'),
            'category_id' => $request->get('category'),
            'creator' => auth()->user()->id,
            'parent_id' => $parent,
            'name' => $request->get('name'),
            'is_directory' => $request->get('is_directory'),
            'due_date' => $request->get('due_date'),
        ]);

        if (!$request->get('is_directory')) {
            $fse->addMediaFromRequest('attachment')->toMediaCollection();
        }

        /**
         *  permission inheritance
         */
        $parentDir = FileSystemEntry::find($parent);
        $groups = $parentDir->groups()->withPivot(['read', 'upload', 'download', 'delete'])->get();
        DB::beginTransaction();
        foreach ($groups as $group){
            $group->fileSystemEntries()->syncWithPivotValues(
                [$fse->id],
                ['read'=>$group->pivot['read'],
                'upload'=>$group->pivot['upload'],
                'download'=>$group->pivot['download'],
                'delete'=>$group->pivot['delete']]
            );
        }
        DB::commit();
        return response($fse);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\FileSystemEntry  $fileSystemEntry
     * @return \Illuminate\Http\Response
     */
    public function show(FileSystemEntry $fileSystemEntry)
    {
        return response($fileSystemEntry->loadMissing(['category', 'group_approval']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\FileSystemEntry  $fileSystemEntry
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, FileSystemEntry $fileSystemEntry)
    {
        $fileSystemEntry->name = $request->get('name');
        $fileSystemEntry->category_id = $request->get('category');
        $fileSystemEntry->group_approval_id = $request->get('group_approval_id');
        $fileSystemEntry->due_date = $request->get('due_date');
        $fileSystemEntry->save();
        return response($fileSystemEntry);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\FileSystemEntry  $fileSystemEntry
     * @return \Illuminate\Http\Response
     */
    public function destroy(FileSystemEntry $fileSystemEntry)
    {
        if ($fileSystemEntry->is_directory) {
            $fileSystemEntry->descendantsAndSelf()->delete();
        } else {
            $fileSystemEntry->delete();
        }

        return response('ok');
    }

    public function goBack(Request $request, FileSystemEntry $fileSystemEntry)
    {
        return response(['documents' => $fileSystemEntry->parent->children()->orderBy('name')->get(), 'parent' => $fileSystemEntry->parent->id]);
    }

    /**
     *
     * @param FileSystemEntry $fileSystemEntry
     * @return \Illuminate\Http\Response
     */
    public function getGroups(FileSystemEntry $fileSystemEntry)
    {
        return response($fileSystemEntry->groups()->withPivot(['read', 'upload', 'download', 'delete'])->get());
    }
}
