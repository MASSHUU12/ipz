<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use App\Jobs\CreateNewLeaderboard;
use Carbon\Carbon;

class CreateNewLeaderboardJobTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function testHandleDoesNotCreateWhenNoHistoricalData()
    {
        (new CreateNewLeaderboard())->handle();
        $this->assertDatabaseCount('air_pollution_leaderboard', 0);
    }

    /** @test */
    public function testHandleCreatesLeaderboardEntriesWithCorrectAQI()
    {
        $today = Carbon::today()->toDateString();
        DB::table('air_pollution_historical_data')->insert([
            [
                'station_id'   => 1,
                'latitude'     => 50.0,
                'longitude'    => 20.0,
                'station_name' => 'Station A',
                'pm25'         => 12.0,
                'created_at'   => "$today 10:00:00",
                'updated_at'   => "$today 10:00:00",
            ],
            [
                'station_id'   => 2,
                'latitude'     => 51.0,
                'longitude'    => 21.0,
                'station_name' => 'Station B',
                'pm25'         => 35.5,
                'created_at'   => "$today 12:00:00",
                'updated_at'   => "$today 12:00:00",
            ],
        ]);

        (new CreateNewLeaderboard())->handle();

        $this->assertDatabaseCount('air_pollution_leaderboard', 2);
        $this->assertDatabaseHas('air_pollution_leaderboard', [
            'station_name'      => 'Station A',
            'air_quality_index' => '50',
        ]);
        $this->assertDatabaseHas('air_pollution_leaderboard', [
            'station_name'      => 'Station B',
            'air_quality_index' => '101',
        ]);
    }
}
