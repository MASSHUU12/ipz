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

    public function test_default_preferences_reflect_migration_defaults()
    {
        $user = User::factory()->create();
        $pref = $user->preference;

        $this->assertNotNull($pref);
        $this->assertEquals('E-mail', $pref->notice_method);
        $this->assertNull($pref->city);
        $this->assertFalse($pref->meteorological_warnings);
        $this->assertFalse($pref->hydrological_warnings);
        $this->assertFalse($pref->air_quality_warnings);
        $this->assertFalse($pref->temperature_warning);
        $this->assertSame('10.00', $pref->temperature_check_value);
    }

    public function test_mass_assigment_protection()
    {
        $pref = new UserPreference();
        $pref->fill([
            'notice_method' => 'Both',
            'user_id' => 123,
        ]);
        $this->assertEquals('Both', $pref->notice_method);
        $this->assertNull($pref->user_id);
    }

    public function test_user_has_one_preference_relation()
    {
        $user = User::factory()->create();
        $pref = $user->preference;
        $this->assertInstanceOf(UserPreference::class, $pref);
        $this->assertEquals($user->id, $pref->user_id);
    }

    public function test_factory_generates_valid_notice_method()
    {
        $valid = ['SMS', 'E-mail', 'Both'];
        foreach (range(1, 10) as $i) {
            $pref = UserPreference::factory()->create();
            $this->assertContains($pref->notice_method, $valid);
        }
    }
}
