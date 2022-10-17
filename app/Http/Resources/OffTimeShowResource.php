<?php

namespace App\Http\Resources;

use App\OffTimeType;
use Illuminate\Http\Resources\Json\JsonResource;

class OffTimeShowResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'start_day' => $this->start_day,
            'end_day'   => $this->end_day,
            'status'    => $this->status,
            'type'      => $this->offTimeType->name,
        ];
    }
}
