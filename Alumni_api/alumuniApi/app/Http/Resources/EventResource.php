<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return[ 
            'id' => $this->id,
            'event_title' => $this->event_title,
            'description' => $this->description,
            'dateTime' => $this->dateTime,
            'location' => $this->location,
            'image' => $this->image,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            //'created_by' => $this->created_by,
            ];
    }
}
