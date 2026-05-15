<?php
// app/Http/Controllers/MediaController.php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file'        => ['required', 'file', 'mimes:jpg,jpeg,png,webp,mp4,mov,glb', 'max:51200'],
            'category_id' => ['nullable', 'integer'],
            'color'       => ['nullable', 'string', 'max:100'],
            'brand'       => ['nullable', 'string', 'max:100'],
            'material'    => ['nullable', 'string', 'max:100'],
            'gender'      => ['nullable', 'string'],
        ]);

        $accountId = session('active_account_id');
        $user      = auth()->user();
        $file      = $request->file('file');

        // Owner/admin → approved directo; seller → pending
        $isManager = \Gate::allows('manage-account-users');

        $path = $file->store("accounts/{$accountId}/media", 'public');

        $media = Media::create([
            'account_id'    => $accountId,
            'uploaded_by'   => $user->id,
            'path'          => $path,
            'disk'          => 'public',
            'type'          => str_starts_with($file->getMimeType(), 'video') ? 'video' : 'photo',
            'mime_type'     => $file->getMimeType(),
            'size'          => $file->getSize(),
            'original_name' => $file->getClientOriginalName(),
            'category_id'   => $request->category_id,
            'color'         => $request->color ? strtoupper($request->color) : null,
            'brand'         => $request->brand ? strtoupper($request->brand) : null,
            'material'      => $request->material ? strtoupper($request->material) : null,
            'gender'        => $request->gender,
            'status'        => $isManager ? 'approved' : 'pending',
            'approved_by'   => $isManager ? $user->id : null,
            'approved_at'   => $isManager ? now() : null,
        ]);

        return response()->json([
            'id'     => $media->id,
            'url'    => $media->url,
            'status' => $media->status,
        ]);
    }

    public function approve(Media $media)
    {
        $accountId = session('active_account_id');
        abort_unless($media->account_id === $accountId, 403);

        $media->update([
            'status'      => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return response()->json(['ok' => true]);
    }
}