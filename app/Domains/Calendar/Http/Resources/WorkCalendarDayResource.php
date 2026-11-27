<?php

namespace App\Domains\Calendar\Http\Resources;

use App\Domains\Calendar\Models\WorkCalendarDay;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin WorkCalendarDay */
class WorkCalendarDayResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date->toDateString(),
            'name' => $this->name,
            'day_type_id' => $this->day_type_id,
            'day_type' => $this->whenLoaded('dayType', fn () => [
                'id' => $this->dayType->id,
                'name' => $this->dayType->name,
                'color' => $this->dayType->color,
                'is_working' => (bool) $this->dayType->is_working,
            ]),
        ];
    }
}
