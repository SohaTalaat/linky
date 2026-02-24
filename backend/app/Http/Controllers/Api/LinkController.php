<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLinkRequest;
use App\Http\Requests\UpdateLinkRequest;
use App\Http\Resources\LinkResource;
use App\Jobs\FetchLinkMetadataJob;
use App\Models\Link;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LinkController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Link::query()
            ->where('user_id', $user->id)
            ->with('tags');

        // Filter
        if ($search = $request->string('search')->trim()->toString()) {

            if (DB::getDriverName() === 'mysql') {
                $query->whereRaw(
                    "MATCH(title, notes, url) AGAINST (? IN BOOLEAN MODE)",
                    [$search . '*']
                );
            } else {
                $query->where(function ($sub) use ($search) {
                    $sub->where('title', 'like', "%{$search}%")
                        ->orWhere('url', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%");
                });
            }
        }

        if ($status = $request->string('status')->trim()->toString()) {
            $query->where('status', $status);
        }

        if ($request->has('favourite')) {
            $fav = filter_var(
                $request->input('favourite'),
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            );
            if (!is_null($fav)) {
                $query->where('is_favourite', $fav);
            }
        }

        if ($tag = $request->input('tag')) {
            $query->whereHas('tags', function ($sub) use ($tag) {
                if (is_numeric($tag)) {
                    $sub->where('tags.id', (int) $tag);
                } else {
                    $sub->where('tags.name', (string) $tag);
                }
            });
        }

        $perPage = (int) ($request->input('per_page', 15));
        $perPage = max(1, min($perPage, 100));

        $links = $query->latest()->paginate($perPage);

        return LinkResource::collection($links);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLinkRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();

        $link = Link::create([
            'user_id' => $user->id,
            'url' => $data['url'],
            'title' => $data['title'] ?? null,
            'notes' => $data['notes'] ?? null,
            'status' => $data['status'] ?? 'saved',
            'is_favourite' => $data['is_favourite'] ?? false,
        ]);

        FetchLinkMetadataJob::dispatch($link->id)->onQueue('metadata');

        if (!empty($data['tags'])) {
            $tagIds = $this->upsertUserTagsAndGetIds($user->id, $data['tags']);
            $link->tags()->sync($tagIds);
        }
        Log::info('STORE HIT', $request->all());
        return (new LinkResource($link->load('tags')))->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Link $link)
    {
        $this->authorize('view', $link);

        return new LinkResource($link->load('tags'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLinkRequest $request, Link $link)
    {
        $this->authorize('update', $link);

        $data = $request->validated();

        $link->fill($data);
        $link->save();

        if (array_key_exists('tags', $data)) {
            $tagIds = $data['tags'] ? $this->upsertUserTagsAndGetIds($request->user()->id, $data['tags']) : [];

            $link->tags()->sync($tagIds);
        }

        return new LinkResource($link->load('tags'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Link $link)
    {
        $this->authorize('delete', $link);

        $link->delete();

        return response()->json([
            'message' => 'Deleted successfully.'
        ]);
    }

    private function upsertUserTagsAndGetIds(int $userId, array $tagNames): array
    {
        $clean = collect($tagNames)
            ->map(fn($t) => trim((string) $t))
            ->filter()
            ->map(fn($t) => mb_strtolower($t))
            ->unique()
            ->values();

        if ($clean->isEmpty()) {
            return [];
        }

        foreach ($clean as $name) {
            Tag::firstOrCreate([
                'user_id' => $userId,
                'name' => $name
            ]);
        }

        return Tag::where('user_id', $userId)
            ->whereIn('name', $clean->all())
            ->pluck('id')
            ->all();
    }

    public function toggleFavourite(Request $request, Link $link)

    {
        $this->authorize('update', $link);

        if ($request->has('is_favourite')) {
            $link->is_favourite = (bool) $request->boolean('is_favourite');
        } else {
            $link->is_favourite = ! $link->is_favourite;
        }

        $link->save();

        return new LinkResource($link->load('tags'));
    }
}
