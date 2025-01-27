<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupResource extends JsonResource
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
            'name' => $this->name,
            'label' => $this->label,
            'price' => $this->price,
            'description' => $this->description,
            'subject' => new SubjectResource($this->whenLoaded('subject')),
            'tasks' => $this->whenLoaded('tasks'),
            'teachers' => $this->whenLoaded('teachers'),
            'students' => $this->whenLoaded('students'),
        ];
    }
}
