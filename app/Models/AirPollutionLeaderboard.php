<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AirPollutionLeaderboard extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'air_pollution_leaderboard';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'station_name',
        'city',
        'air_quality_index',
        'pm10',
        'pm25',
        'no2',
        'so2',
        'o3',
        'co',
        'timestamp'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
     protected $casts = [
        'station_name' => 'string',
        'city' => 'string',
        'air_quality_index' => 'string',
        'pm10' => 'float',
        'pm25' => 'float',
        'no2' => 'float',
        'so2' => 'float',
        'o3' => 'float',
        'co' => 'float',
        'timestamp' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
     ];
}
