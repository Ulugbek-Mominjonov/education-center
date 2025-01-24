<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeacherResource extends JsonResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone' => $this->phone,
            'degree' => $this->degree,
            'salary' => $this->salary,
            "user" => $this->whenLoaded('user', function () {
                return new UserResource($this->user->load('userType', 'roles'));
            }),
            "subjects" => $this->whenLoaded('subjects', function () {
                return $this->subjects->setVisible(['id', 'name']);
            }),
            "groups" => $this->whenLoaded('groups', function () {
                return $this->groups->setVisible(['id', 'name', 'subject_id']);
            }),

        ];
    }
}
