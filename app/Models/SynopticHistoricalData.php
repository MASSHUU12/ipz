<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SynopticHistoricalData extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'synoptic_historical_data';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'station_id',
        'station_name',
        'measurement_date',
        'measurement_hour',
        'temperature',
        'wind_speed',
        'wind_direction',
        'relative_humidity',
        'rainfall_total',
        'pressure',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'station_id' => 'int',
        'measurement_date' => 'datetime',
        'measurement_hour' => 'int',
        'temperature' => 'float',
        'wind_speed' => 'int',
        'wind_direction' => 'int',
        'relative_humidity' => 'float',
        'rainfall_total' => 'float',
        'pressure' => 'float',
    ];
}
