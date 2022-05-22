<?php

namespace NavidBakhtiary\ToDo\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TaskDetailsResource extends JsonResource
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
            'status' => $this->status,
            'created at' => $this->created_at,
            'last updated at' => $this->updated_at,
            'labels' => LabelsIndexResource::collection($this->labels)
        ];
    }
}
