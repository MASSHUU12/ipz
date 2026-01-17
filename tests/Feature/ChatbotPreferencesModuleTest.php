<?php

use App\Chatbot\Modules\UserPreferencesModule;
use App\Http\Requests\MessageChatbotRequest;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('setCity updates user city preference', function () {
    $user = User::factory()->create();
    
    $request = Mockery::mock(MessageChatbotRequest::class);
    $request->shouldReceive('user')->andReturn($user);
    
    $matches = [
        0 => 'set my city to Warsaw',
        1 => 'set',
        2 => 'my ',
        3 => 'city',
        4 => 'Warsaw',
    ];
    
    $response = UserPreferencesModule::setCity($matches, $request);
    
    expect($response)->toContain('Warsaw')
        ->and($user->fresh()->preference->city)->toBe('Warsaw');
});

test('setCity creates preference if not exists', function () {
    $user = User::factory()->create();
    // Ensure no preference exists
    $user->preference()->delete();
    
    $request = Mockery::mock(MessageChatbotRequest::class);
    $request->shouldReceive('user')->andReturn($user);
    
    $matches = [
        0 => 'set my city to Krakow',
        1 => 'set',
        2 => 'my ',
        3 => 'city',
        4 => 'Krakow',
    ];
    
    $response = UserPreferencesModule::setCity($matches, $request);
    
    expect($response)->toContain('Krakow')
        ->and($user->fresh()->preference)->not->toBeNull()
        ->and($user->fresh()->preference->city)->toBe('Krakow');
});

test('setCity requires authentication', function () {
    $request = Mockery::mock(MessageChatbotRequest::class);
    $request->shouldReceive('user')->andReturn(null);
    
    $matches = [
        0 => 'set my city to Warsaw',
        1 => 'set',
        2 => 'my ',
        3 => 'city',
        4 => 'Warsaw',
    ];
    
    $response = UserPreferencesModule::setCity($matches, $request);
    
    expect($response)->toContain('logged in');
});

test('setNotificationMethod updates notification method to SMS', function () {
    $user = User::factory()->create();
    
    $request = Mockery::mock(MessageChatbotRequest::class);
    $request->shouldReceive('user')->andReturn($user);
    
    $matches = [
        0 => 'set my notification method to sms',
        1 => 'set',
        2 => 'my ',
        3 => 'notification',
        4 => 'method',
        5 => 'sms',
    ];
    
    $response = UserPreferencesModule::setNotificationMethod($matches, $request);
    
    expect($response)->toContain('SMS')
        ->and($user->fresh()->preference->notice_method)->toBe('SMS');
});

test('setNotificationMethod updates notification method to Email', function () {
    $user = User::factory()->create();
    
    $request = Mockery::mock(MessageChatbotRequest::class);
    $request->shouldReceive('user')->andReturn($user);
    
    $matches = [
        0 => 'set my notification method to email',
        1 => 'set',
        2 => 'my ',
        3 => 'notification',
        4 => 'method',
        5 => 'email',
    ];
    
    $response = UserPreferencesModule::setNotificationMethod($matches, $request);
    
    expect($response)->toContain('E-mail')
        ->and($user->fresh()->preference->notice_method)->toBe('E-mail');
});

test('setNotificationMethod updates notification method to Both', function () {
    $user = User::factory()->create();
    
    $request = Mockery::mock(MessageChatbotRequest::class);
    $request->shouldReceive('user')->andReturn($user);
    
    $matches = [
        0 => 'set my notification method to both',
        1 => 'set',
        2 => 'my ',
        3 => 'notification',
        4 => 'method',
        5 => 'both',
    ];
    
    $response = UserPreferencesModule::setNotificationMethod($matches, $request);
    
    expect($response)->toContain('Both')
        ->and($user->fresh()->preference->notice_method)->toBe('Both');
});

test('toggleWarning enables meteorological warnings', function () {
    $user = User::factory()->create();
    
    $request = Mockery::mock(MessageChatbotRequest::class);
    $request->shouldReceive('user')->andReturn($user);
    
    $matches = [
        0 => 'enable meteorological warnings',
        1 => 'enable',
        2 => 'meteorological',
    ];
    
    $response = UserPreferencesModule::toggleWarning($matches, $request);
    
    expect($response)->toContain('enabled')
        ->and($user->fresh()->preference->meteorological_warnings)->toBeTrue();
});

test('toggleWarning disables hydrological warnings', function () {
    $user = User::factory()->create();
    $user->preference->hydrological_warnings = true;
    $user->preference->save();
    
    $request = Mockery::mock(MessageChatbotRequest::class);
    $request->shouldReceive('user')->andReturn($user);
    
    $matches = [
        0 => 'disable hydrological warnings',
        1 => 'disable',
        2 => 'hydrological',
    ];
    
    $response = UserPreferencesModule::toggleWarning($matches, $request);
    
    expect($response)->toContain('disabled')
        ->and($user->fresh()->preference->hydrological_warnings)->toBeFalse();
});

test('toggleWarning enables air quality warnings', function () {
    $user = User::factory()->create();
    
    $request = Mockery::mock(MessageChatbotRequest::class);
    $request->shouldReceive('user')->andReturn($user);
    
    $matches = [
        0 => 'turn on air quality warnings',
        1 => 'turn on',
        2 => 'air quality',
    ];
    
    $response = UserPreferencesModule::toggleWarning($matches, $request);
    
    expect($response)->toContain('enabled')
        ->and($user->fresh()->preference->air_quality_warnings)->toBeTrue();
});

test('setTemperatureWarning enables temperature warnings', function () {
    $user = User::factory()->create();
    
    $request = Mockery::mock(MessageChatbotRequest::class);
    $request->shouldReceive('user')->andReturn($user);
    
    $matches = [
        0 => 'enable temperature warnings',
        1 => 'enable',
        2 => '',
    ];
    
    $response = UserPreferencesModule::setTemperatureWarning($matches, $request);
    
    expect($response)->toContain('enabled')
        ->and($user->fresh()->preference->temperature_warning)->toBeTrue();
});

test('setTemperatureWarning enables temperature warnings with threshold', function () {
    $user = User::factory()->create();
    
    $request = Mockery::mock(MessageChatbotRequest::class);
    $request->shouldReceive('user')->andReturn($user);
    
    $matches = [
        0 => 'enable temperature warnings at 25.5',
        1 => 'enable',
        2 => '25.5',
    ];
    
    $response = UserPreferencesModule::setTemperatureWarning($matches, $request);
    
    expect($response)->toContain('enabled')
        ->and($response)->toContain('25.5')
        ->and($user->fresh()->preference->temperature_warning)->toBeTrue()
        ->and($user->fresh()->preference->temperature_check_value)->toBe('25.50');
});

test('setTemperatureWarning disables temperature warnings', function () {
    $user = User::factory()->create();
    $user->preference->temperature_warning = true;
    $user->preference->save();
    
    $request = Mockery::mock(MessageChatbotRequest::class);
    $request->shouldReceive('user')->andReturn($user);
    
    $matches = [
        0 => 'disable temperature warnings',
        1 => 'disable',
        2 => '',
    ];
    
    $response = UserPreferencesModule::setTemperatureWarning($matches, $request);
    
    expect($response)->toContain('disabled')
        ->and($user->fresh()->preference->temperature_warning)->toBeFalse();
});

test('setTemperatureThreshold updates temperature threshold', function () {
    $user = User::factory()->create();
    
    $request = Mockery::mock(MessageChatbotRequest::class);
    $request->shouldReceive('user')->andReturn($user);
    
    $matches = [
        0 => 'set temperature threshold to 30',
        1 => 'set',
        2 => 'threshold',
        3 => '30',
    ];
    
    $response = UserPreferencesModule::setTemperatureThreshold($matches, $request);
    
    expect($response)->toContain('30.0')
        ->and($user->fresh()->preference->temperature_check_value)->toBe('30.00');
});

test('setTemperatureThreshold updates decimal threshold', function () {
    $user = User::factory()->create();
    
    $request = Mockery::mock(MessageChatbotRequest::class);
    $request->shouldReceive('user')->andReturn($user);
    
    $matches = [
        0 => 'set temperature threshold to 22.7',
        1 => 'set',
        2 => 'threshold',
        3 => '22.7',
    ];
    
    $response = UserPreferencesModule::setTemperatureThreshold($matches, $request);
    
    expect($response)->toContain('22.7')
        ->and($user->fresh()->preference->temperature_check_value)->toBe('22.70');
});

test('listPreferences shows all preferences correctly', function () {
    $user = User::factory()->create();
    $user->preference->city = 'Warsaw';
    $user->preference->notice_method = 'Both';
    $user->preference->meteorological_warnings = true;
    $user->preference->hydrological_warnings = false;
    $user->preference->air_quality_warnings = true;
    $user->preference->temperature_warning = true;
    $user->preference->temperature_check_value = 25.5;
    $user->preference->save();
    
    $request = Mockery::mock(MessageChatbotRequest::class);
    $request->shouldReceive('user')->andReturn($user);
    
    $matches = [
        0 => 'show my preferences',
        1 => 'show',
        2 => 'my ',
        3 => 'preferences',
    ];
    
    $response = UserPreferencesModule::listPreferences($matches, $request);
    
    expect($response)->toContain('Warsaw')
        ->and($response)->toContain('Both')
        ->and($response)->toContain('Meteorological: ✅ Enabled')
        ->and($response)->toContain('Hydrological: ❌ Disabled')
        ->and($response)->toContain('Air Quality: ✅ Enabled')
        ->and($response)->toContain('Temperature: ✅ Enabled')
        ->and($response)->toContain('25.5');
});
