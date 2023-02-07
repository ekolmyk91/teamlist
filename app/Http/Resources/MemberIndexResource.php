<?php

namespace App\Http\Resources;

use App\OffTime;
use Illuminate\Http\Resources\Json\JsonResource;

class MemberIndexResource extends JsonResource
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
            'id'            => $this->user_id,
            'fullname'      => $this->name . ' ' . $this->surname,
            'off_time_stat' => OffTime::getOffTimeDaysCount($this->user_id),
            'off_time'      => OffTimeShowResource::collection($this->offTimeList->where('status', 'approved')->sortBy('start_day'))->groupBy('type_id')
        ];
    }
}
