<?php

namespace NavidBakhtiary\ToDo\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserTasksIndexResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'labels' => LabelsIndexResource::collection($this->labels)
        ];
    }
}
