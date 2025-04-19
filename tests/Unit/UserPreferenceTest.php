<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPreferenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_casts_fields_to_correct_types()
    {
        $pref = new UserPreference([
            'notice_method' => 'email',
            'city' => 'Warszawa',
            'meteorological_warnings' => 1,
            'hydrological_warnings' => 0,
            'air_quality_warnings' => '1',
            'temperature_warning' => '0',
            'temperature_check_value' => 15.5,
        ]);

        $this->assertIsBool($pref->meteorological_warnings);
        $this->assertFalse($pref->hydrological_warnings);
        $this->assertTrue($pref->air_quality_warnings);
        $this->assertFalse($pref->temperature_warning);
        $this->assertNotNull($pref->temperature_check_value);
        $this->assertSame('15.50', $pref->temperature_check_value);
    }
}
