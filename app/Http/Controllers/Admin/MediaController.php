<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Services\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class MediaController extends Controller
{
    public function __construct(private MediaService $media) {}

    public function index(Request $request): View
    {
        return view('admin.media.index', [
            'assets' => Media::query()
                ->with('uploader')
                ->when($request->string('q')->toString(), fn ($query, $term) => $query->where(function ($inner) use ($term) {
                    $inner->where('name', 'like', "%{$term}%")->orWhere('file_name', 'like', "%{$term}%");
                }))
                ->when($request->string('type')->toString() === 'image', fn ($query) => $query->where('mime_type', 'like', 'image/%'))
                ->latest('id')
                ->paginate(24)
                ->withQueryString(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,webp,avif,pdf,svg'],
            'alt_text' => ['nullable', 'string', 'max:180'],
        ]);

        $this->media->upload(
            $request->file('file'),
            $request->user(),
            null,
            $request->string('alt_text')->toString() ?: null
        );

        return back()->with('status', 'Asset uploaded.');
    }

    public function update(Request $request, Media $medium): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'alt_text' => ['nullable', 'string', 'max:180'],
            'caption' => ['nullable', 'string', 'max:300'],
        ]);

        $medium->update($data);

        return back()->with('status', 'Asset details saved.');
    }

    public function destroy(Media $medium): RedirectResponse
    {
        try {
            $this->media->delete($medium);
        } catch (RuntimeException $e) {
            return back()->with('error', 'This asset is still used by a page or product, so it cannot be deleted yet.');
        }

        return back()->with('status', 'Asset deleted.');
    }

    /** JSON feed for the builder's media picker. */
    public function picker(Request $request): JsonResponse
    {
        return response()->json([
            'assets' => $this->media->recent(60),
        ]);
    }
}
