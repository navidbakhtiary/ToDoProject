<?php

namespace NavidBakhtiary\ToDo\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LabelsIndexResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'tasks count' => $this->user_tasks_count
        ];
    }
}
