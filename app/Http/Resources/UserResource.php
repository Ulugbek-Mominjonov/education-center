<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'user_name' => $this->user_name,
            'user_type_id' => $this->user_type_id,
            'user_type_name' => $this->whenLoaded('userType', function () {
                return $this->userType->name;
            }),
            'user_roles' => $this->whenLoaded('roles', function () {
                return $this->roles->pluck('name');
            }),
            'is_active' => $this->is_active,
            'is_attach' => $this->is_attach,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
