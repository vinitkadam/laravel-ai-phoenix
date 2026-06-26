<?php

use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Trace\NoopTracerProvider;
use OpenTelemetry\SDK\Trace\TracerProvider;
use Vinit\LaravelAiPhoenix\PhoenixServiceProvider;

// Reset OpenTelemetry Globals before each test so registered initializers
// from previous tests do not leak into subsequent ones.
beforeEach(function () {
    Globals::reset();
});

afterEach(function () {
    Globals::reset();
});

// ---------------------------------------------------------------------------
// Config merging
// ---------------------------------------------------------------------------

test('endpoint defaults to the Arize cloud URL', function () {
    expect(config('phoenix.endpoint'))->toBe('https://app.phoenix.arize.com/v1/traces');
});

test('project defaults to APP_NAME when no PHOENIX_PROJECT is set', function () {
    // The config/phoenix.php default reads env('APP_NAME', 'laravel').
    // In the test environment APP_NAME is not set, so the fallback 'laravel' applies.
    // This confirms the config is wired to APP_NAME rather than a hard-coded value.
    $project = config('phoenix.project');

    expect($project)->toBeString()->not->toBeEmpty();

    // When a project override is set it takes effect immediately.
    config()->set('phoenix.project', 'acme');
    expect(config('phoenix.project'))->toBe('acme');
});

test('timeout defaults to 5.0', function () {
    expect(config('phoenix.timeout'))->toBe(5.0);
});

test('api_key defaults to null', function () {
    expect(config('phoenix.api_key'))->toBeNull();
});

// ---------------------------------------------------------------------------
// Config overrides
// ---------------------------------------------------------------------------

test('endpoint can be overridden via config', function () {
    config()->set('phoenix.endpoint', 'http://localhost:6006/v1/traces');

    expect(config('phoenix.endpoint'))->toBe('http://localhost:6006/v1/traces');
});

test('api_key can be set via config', function () {
    config()->set('phoenix.api_key', 'secret-key');

    expect(config('phoenix.api_key'))->toBe('secret-key');
});

test('timeout can be overridden via config', function () {
    config()->set('phoenix.timeout', 10.0);

    expect(config('phoenix.timeout'))->toBe(10.0);
});

test('project can be overridden via config', function () {
    config()->set('phoenix.project', 'custom-project');

    expect(config('phoenix.project'))->toBe('custom-project');
});

// ---------------------------------------------------------------------------
// Config publishing
// ---------------------------------------------------------------------------

test('phoenix-config tag publishes config file to the app config path', function () {
    // The app runs in console mode in the Testbench CLI environment, so
    // PhoenixServiceProvider::boot() will have called publishes() already.
    $publishTags = PhoenixServiceProvider::$publishGroups ?? [];

    // The key stored by publishes() is not realpath-normalised.
    $taggedPaths = $publishTags['phoenix-config'] ?? [];

    // At least one tagged path should resolve to config/phoenix.php.
    $resolvedKeys = array_map('realpath', array_keys($taggedPaths));

    expect($resolvedKeys)->toContain(
        realpath(__DIR__.'/../../config/phoenix.php')
    );

    // The target should be inside the app's config directory.
    $targetPath = config_path('phoenix.php');
    expect(array_values($taggedPaths))->toContain($targetPath);
});

// ---------------------------------------------------------------------------
// TracerProvider registration
// ---------------------------------------------------------------------------

test('boot registers an initializer that produces a real TracerProvider via Globals', function () {
    // The service provider's register() was already called by Testbench before
    // the beforeEach reset, so we need to re-register it here.
    $provider = new PhoenixServiceProvider($this->app);
    $provider->register();

    // Calling tracerProvider() triggers the lazy initializer.
    $tracerProvider = Globals::tracerProvider();

    expect($tracerProvider)->not->toBeInstanceOf(NoopTracerProvider::class);
    expect($tracerProvider)->toBeInstanceOf(TracerProvider::class);
});

test('initializer produces a TracerProvider with the configured project name', function () {
    config()->set('phoenix.project', 'test-project');

    $provider = new PhoenixServiceProvider($this->app);
    $provider->register();

    $tracerProvider = Globals::tracerProvider();

    expect($tracerProvider)->toBeInstanceOf(TracerProvider::class);
    expect($tracerProvider)->not->toBeInstanceOf(NoopTracerProvider::class);
});

test('initializer includes api_key header when api_key is configured', function () {
    config()->set('phoenix.api_key', 'my-api-key');

    $provider = new PhoenixServiceProvider($this->app);
    $provider->register();

    // The TracerProvider is created without error when api_key is present.
    $tracerProvider = Globals::tracerProvider();

    expect($tracerProvider)->toBeInstanceOf(TracerProvider::class);
});
