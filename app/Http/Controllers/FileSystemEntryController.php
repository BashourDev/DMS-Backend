<?php

namespace App\Http\Controllers;

use App\Models\FileSystemEntry;
use App\Models\Group;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
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
        if (auth()->user()->is_admin) {
            return response(['documents' => $fileSystemEntry->children()->with(['media:id,model_type,model_id,disk,file_name'])->orderBy('name')->get(), 'parent' => $fileSystemEntry->id]);
        } else {
            return response(['documents' => $fileSystemEntry->children()->with('permissions',function ( $query){
                $query->selectRaw('id,group_id,file_system_entry_id,
            bit_or(`read`) as `read`
            , bit_or(upload) as upload
            , bit_or(download) as download
            , bit_or(`delete`) as `delete`')->groupBy('file_system_entry_id');
            })->with('media:id,model_type,model_id,disk,file_name')->whereRelation('permissions','read',1 )
                ->orderBy('name')->get(), 'parent' => $fileSystemEntry->id]);
        }


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
        $this->authorize('upload',[$parent]);
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
            $group->fileSystemEntries()->attach(
                $fse->id,
                ['read'=>$group->pivot['read'],
                'upload'=>$group->pivot['upload'],
                'download'=>$group->pivot['download'],
                'delete'=>$group->pivot['delete']]
            );
        }
        DB::commit();
//        return response($fse->loadMissing('media:id,model_type,model_id,disk,file_name'));
        if (auth()->user()->is_admin) {
            return response($fse->loadMissing(['media:id,model_type,model_id,disk,file_name']));
        } else {
            return response($fse->loadMissing(['media:id,model_type,model_id,disk,file_name', 'permissions' => function ( $query){
                $query->selectRaw('id,group_id,file_system_entry_id,
            bit_or(`read`) as `read`
            , bit_or(upload) as upload
            , bit_or(download) as download
            , bit_or(`delete`) as `delete`')->groupBy('file_system_entry_id');
            }]));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\FileSystemEntry  $fileSystemEntry
     * @return \Illuminate\Http\Response
     */
    public function show(FileSystemEntry $fileSystemEntry)
    {
        $this->authorize('view',[$fileSystemEntry]);
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
        $this->authorize('upload',[$fileSystemEntry]);
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
        $this->authorize('delete',[$fileSystemEntry]);
        if ($fileSystemEntry->is_directory) {
            $fileSystemEntry->descendantsAndSelf()->delete();
        } else {
            $fileSystemEntry->delete();
        }

        return response('ok');
    }

    public function goBack(Request $request, FileSystemEntry $fileSystemEntry)
    {
        if (auth()->user()->is_admin) {
                    return response(['documents' => $fileSystemEntry->parent->children()->orderBy('name')->get(), 'parent' => $fileSystemEntry->parent->id]);
        } else {
            $parent = $fileSystemEntry->parent;
            return response(['documents' => $parent->children()->with('permissions',function ( $query){
                $query->selectRaw('id,group_id,file_system_entry_id,
            bit_or(`read`) as `read`
            , bit_or(upload) as upload
            , bit_or(download) as download
            , bit_or(`delete`) as `delete`')->groupBy('file_system_entry_id');
            })->whereRelation('permissions','read',1 )
                ->orderBy('name')->get(), 'parent' => $parent->id]);
        }
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

    public function versions(Request $request, FileSystemEntry $fileSystemEntry)
    {
        return response($fileSystemEntry->loadMissing('media'));
    }

    public function add_version(Request $request, FileSystemEntry $fileSystemEntry)
    {
        $this->authorize('upload',[$fileSystemEntry]);

        $fileSystemEntry->addMediaFromRequest('attachment')->toMediaCollection();
        return response($fileSystemEntry->loadMissing('media'));
    }

    public function delete_version(Request $request, FileSystemEntry $fileSystemEntry, $version)
    {
        $this->authorize('delete',[$fileSystemEntry]);

        $fileSystemEntry->media()->find($version)->delete();
        return response($fileSystemEntry->loadMissing('media'));
    }

    public function download(Request $request,FileSystemEntry $fileSystemEntry, Media $media)
    {
        $this->authorize('download',[$fileSystemEntry]);
        return $media;
    }

    public function download_latest(Request $request, FileSystemEntry $fileSystemEntry)
    {
        return [$fileSystemEntry->media()->latest('updated_at')->get()->last()];
    }

    public function manipulateGroupsAndPermissions(Request $request, FileSystemEntry $fileSystemEntry){
        $fses = $fileSystemEntry->descendantsAndSelf()->get();

        DB::beginTransaction();
        foreach ($fses as $fse){

            /**
             * detaching groups from this file system entry
             */
            $fse->groups()->detach(json_decode($request->get('deleted_groups')));

            /**
             * attaching new groups to this file system entry
             */
            foreach (json_decode($request->get('new_groups')) as $newGroup){
                $fse->groups()->attach($newGroup->group_id,[
                    'read'=>$newGroup->read,
                    'upload'=>$newGroup->upload,
                    'download'=>$newGroup->download,
                    'delete'=>$newGroup->delete
                ]);
            }

            /**
             * editing existing permissions between this file system entry and its groups
             */
            foreach (json_decode($request->get('updated_groups')) as $oldGroup){
                $fse->groups()->updateExistingPivot($oldGroup->group_id,[
                    'read'=>$oldGroup->read,
                    'upload'=>$oldGroup->upload,
                    'download'=>$oldGroup->download,
                    'delete'=>$oldGroup->delete
                ]);
            }
        }

        DB::commit();
    }

    public function linkableGroups(FileSystemEntry $fileSystemEntry){
        $linkableGroups = Group::query()->whereDoesntHave('fileSystemEntries',function(Builder $query) use ($fileSystemEntry) {
            $query->where('file_system_entries.id','=',$fileSystemEntry->id);
        })->get();
        return response($linkableGroups);
    }
}
