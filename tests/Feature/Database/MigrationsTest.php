<?php

namespace Tests\Feature\Database;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class MigrationsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function air_pollution_historical_data_table_structure()
    {
        // Tabela istnieje
        $this->assertTrue(
            Schema::hasTable('air_pollution_historical_data'),
            'Table air_pollution_historical_data does not exist.'
        );

        // Sprawdź kolumny
        $columns = [
            'id',
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
            'created_at',
            'updated_at',
        ];
        foreach ($columns as $col) {
            $this->assertTrue(
                Schema::hasColumn('air_pollution_historical_data', $col),
                "Missing column {$col} in air_pollution_historical_data."
            );
        }

        // Sprawdź istnienie indeksu na station_id
        $indexes = DB::select(
            "SHOW INDEX FROM `air_pollution_historical_data` WHERE Column_name = ?",
            ['station_id']
        );
        $this->assertNotEmpty(
            $indexes,
            'Missing index on station_id in air_pollution_historical_data.'
        );
    }

    /** @test */
    public function air_pollution_leaderboard_table_structure()
    {
        // Tabela istnieje
        $this->assertTrue(
            Schema::hasTable('air_pollution_leaderboard'),
            'Table air_pollution_leaderboard does not exist.'
        );

        // Sprawdź kolumny
        $columns = [
            'id',
            'station_name',
            'city',
            'air_quality_index',
            'pm10',
            'pm25',
            'no2',
            'so2',
            'o3',
            'co',
            'timestamp',
            'created_at',
            'updated_at',
        ];
        foreach ($columns as $col) {
            $this->assertTrue(
                Schema::hasColumn('air_pollution_leaderboard', $col),
                "Missing column {$col} in air_pollution_leaderboard."
            );
        }
    }

    /** @test */
    public function can_insert_and_retrieve_historical_data()
    {
        // Przygotuj dane
        $payload = [
            'station_id'          => 42,
            'latitude'            => 50.062006,
            'longitude'           => 19.940984,
            'station_name'        => 'Test Station',
            'air_quality_index'   => 'Good',
            'pm10'                => 12.5,
            'pm25'                => 8.3,
            'no2'                 => 5.2,
            'so2'                 => 2.1,
            'o3'                  => 15.7,
            'co'                  => 0.4,
            'measurements'        => json_encode(['pm10' => 12.5]),
            'forecasts'           => json_encode(['pm10' => 14.0]),
            'created_at'          => now(),
            'updated_at'          => now(),
        ];

        // Wstaw rekord
        DB::table('air_pollution_historical_data')->insert($payload);

        // Odczytaj i porównaj
        $record = DB::table('air_pollution_historical_data')
            ->where('station_id', 42)
            ->first();

        $this->assertEquals(42, $record->station_id);
        $this->assertSame('Test Station', $record->station_name);
        $this->assertEquals(12.5, (float) $record->pm10);
        $this->assertEquals(
            ['pm10' => 12.5],
            json_decode($record->measurements, true)
        );
        $this->assertEquals(
            ['pm10' => 14.0],
            json_decode($record->forecasts, true)
        );
    }

    /** @test */
    public function can_insert_and_retrieve_leaderboard_data()
    {
        // Przygotuj dane
        $payload = [
            'station_name'      => 'Rank Station',
            'city'              => 'Warsaw',
            'air_quality_index' => 'Moderate',
            'pm10'              => 20.0,
            'pm25'              => 10.0,
            'no2'               => 9.0,
            'so2'               => 1.0,
            'o3'                => 30.0,
            'co'                => 0.7,
            'timestamp'         => now(),
            'created_at'        => now(),
            'updated_at'        => now(),
        ];

        // Wstaw rekord
        DB::table('air_pollution_leaderboard')->insert($payload);

        // Odczytaj i porównaj
        $record = DB::table('air_pollution_leaderboard')
            ->where('station_name', 'Rank Station')
            ->first();

        $this->assertSame('Warsaw', $record->city);
        $this->assertEquals(30.0, (float) $record->o3);
        $this->assertEquals('Moderate', $record->air_quality_index);
    }
}
