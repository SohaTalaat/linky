<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttachTagRequest;
use App\Http\Resources\LinkResource;
use App\Models\Link;
use App\Models\Tag;
use Illuminate\Http\Request;

class LinkTagController extends Controller
{
    public function store(AttachTagRequest $request, Link $link)
    {
        $this->authorize('update', $link);

        $userId = $request->user()->id;
        $names = $request->validated()['tags'];
        $tagIds = $this->upsertUsertagsAndGetIds($userId, $names);

        $link->tags()->syncWithoutDetaching($tagIds);

        return new LinkResource($link->load('tags'));
    }

    public function destroy(Request $request, Link $link, Tag $tag)
    {
        $this->authorize('update', $link);

        if ($tag->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Forbidden'
            ], 403);
        }
    }

    private function upsertUserTagsAndGetIds(int $userId, array $tagNames): array
    {
        $clean = collect($tagNames)
            ->map(fn($t) => trim((string) $t))
            ->filter()
            ->map(fn($t) => mb_strtolower($t))
            ->unique()
            ->values();

        if ($clean->isEmpty()) return [];

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
}
