<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request)
    {
        return [
            'id' => (string) $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'image' => $this->image,
            'activity' => $this->activity,
            'wallet' => $this->wallet,
            'role' => $this->role,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at
        ];
    }
}
