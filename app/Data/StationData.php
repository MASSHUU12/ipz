<?php

namespace App\Data;

final class StationData
{
    public function __construct(
        public int    $id,
        public string $name,
        public float  $latitude,
        public float  $longitude,
        public ?int   $communeId,
        public ?string $address = null,
        public ?string $city    = null,
        public ?string $district= null,
        public ?string $province= null,
    ) {}

    /**
     * @param array<int,mixed> $api
     */
    public static function fromApi(array $api): self
    {
        return new self(
            id:          $api['id'],
            name:        $api['stationName'],
            latitude:    (float)$api['gegrLat'],
            longitude:   (float)$api['gegrLon'],
            communeId:   $api['city']['commune']['communeId'] ?? null,
            address:     $api['addressStreet'] ?? null,
            city:        $api['city']['name'] ?? null,
            district:    $api['city']['commune']['districtName'] ?? null,
            province:    $api['city']['commune']['provinceName'] ?? null,
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'id'        => $this->id,
            'name'      => $this->name,
            'latitude'  => $this->latitude,
            'longitude' => $this->longitude,
            'address'   => $this->address,
            'city'      => $this->city,
            'district'  => $this->district,
            'province'  => $this->province,
        ];
    }
}
