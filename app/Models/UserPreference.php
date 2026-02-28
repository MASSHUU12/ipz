<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{
    use HasFactory;

    protected $table = 'user_preferences';

    protected $primaryKey = 'user_id';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'notice_method',
        'city',
        'meteorological_warnings',
        'hydrological_warnings',
        'air_quality_warnings',
        'temperature_warning',
        'temperature_check_value',
        'locale'
    ];

    protected $casts = [
        'meteorological_warnings' => 'boolean',
        'hydrological_warnings' => 'boolean',
        'air_quality_warnings' => 'boolean',
        'temperature_warning' => 'boolean',
        'temperature_check_value' => 'decimal:2',
    ];

    /**
     * Get the user that owns the preference.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
