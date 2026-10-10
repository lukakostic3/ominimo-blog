<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
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
            'comment' => $this->comment,
            'author_name' => $this->authorName(),
            'is_guest' => is_null($this->user_id),
            'created_at' => $this->created_at->toIso8601String(),
            'can' => [
                'delete' => $request->user()?->can('delete', $this->resource) ?? false,
            ],
        ];
    }
}
