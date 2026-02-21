<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LinkResource;
use App\Models\Link;
use Illuminate\Http\Request;

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
            $query->where(function ($sub) use ($search) {
                $sub->where('title', 'like', "%{$search}%")
                    ->orWhere('url', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        if ($status = $request->string('status')->trim()->toString()) {
            $query->where('status', $status);
        }

        if ($request->has('favourite')) {
            $fav = filter_var($request->input('favourite', FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE));

            if (!is_null($fav)) {
                $query->where('is_favorite', $fav);
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
