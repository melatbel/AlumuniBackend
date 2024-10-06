<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobResource extends JsonResource
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
            'title' => $this->title,
            'company_name' => $this->company_name, 
            'description' => $this->description,
            'location' => $this->location,
            'image' => $this->image,
            'deadline' => $this->deadline,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            ];
    }
}
