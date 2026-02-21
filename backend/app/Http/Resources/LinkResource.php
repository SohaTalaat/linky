<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LinkResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'url' => $this->url,
            'title' => $this->title,
            'description' => $this->description,
            'image' => $this->image,
            'site_name' => $this->site_name,
            'favicon' => $this->favicon,
            'notes' => $this->notes,
            'status' => $this->status,
            'is_favourite' => (bool) $this->is_favourite,
            'last_opened_at' => optional($this->last_opened_at)->toISOString(),
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
            'tags' => $this->whenLoaded(
                'tags',
                fn() =>
                $this->tags->map(fn($t) => ['id' => $t->id, 'name' => $t->name])->values()
            )
        ];
    }
}
