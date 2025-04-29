<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AirPollutionHistoricalData extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'air_pollution_historical_data';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'station_id',
        'latitude',
        'longitude',
        'station_name',
        'air_quality_index',
        'pm10',
        'pm25',
        'no2',
        'so2',
        'o3',
        'co',
        'measurements',
        'forecasts',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'pm10' => 'float',
        'pm25' => 'float',
        'no2' => 'float',
        'so2' => 'float',
        'o3' => 'float',
        'co' => 'float',
        'measurements' => 'array',
        'forecasts' => 'array',
    ];
}
