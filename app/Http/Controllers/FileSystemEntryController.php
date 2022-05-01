<?php

namespace App\Http\Controllers;

use App\Models\FileSystemEntry;
use Illuminate\Http\Request;
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
        return response(['documents' => $fileSystemEntry->children()->orderBy('name')->get(), 'parent' => $fileSystemEntry->id]);
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

        if ($request->get('attachment')) {
            $fse->addMediaFromRequest('attachment');
        }

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
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\FileSystemEntry  $fileSystemEntry
     * @return \Illuminate\Http\Response
     */
    public function destroy(FileSystemEntry $fileSystemEntry)
    {
        //
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
