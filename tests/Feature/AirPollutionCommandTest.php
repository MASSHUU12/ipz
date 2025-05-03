<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use App\Jobs\StoreCurrentAirPollution;
use App\Jobs\CreateNewLeaderboard;


class AirPollutionCommandTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function testStoreCommandDispatchesJob()
    {
        Bus::fake();
        $this->artisan('airpollution:store')
            ->expectsOutput('StoreCurrentAirPollution job has been dispatched.')
            ->assertExitCode(0);
        Bus::assertDispatched(StoreCurrentAirPollution::class);
    }

    /** @test */
    public function testLeaderboardCommandDispatchesJob()
    {
        Bus::fake();
        $this->artisan('airpollution:leaderboard')
            ->expectsOutput('CreateNewLeaderboard job has been dispatched.')
            ->assertExitCode(0);
        Bus::assertDispatched(CreateNewLeaderboard::class);
    }
}
