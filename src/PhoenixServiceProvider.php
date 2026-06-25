<?php

namespace Vinit\LaravelAiPhoenix;

use Illuminate\Support\ServiceProvider;
use OpenTelemetry\API\Common\Time\Clock;
use OpenTelemetry\API\Globals;
use OpenTelemetry\Contrib\Otlp\ContentTypes;
use OpenTelemetry\Contrib\Otlp\OtlpHttpTransportFactory;
use OpenTelemetry\Contrib\Otlp\SpanExporter;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;

class PhoenixServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/phoenix.php', 'phoenix');

        // Register the TracerProvider before the telemetry package resolves its driver,
        // so Globals::tracerProvider() returns the Phoenix-backed provider when the
        // otel driver calls it on first use.
        Globals::registerInitializer(function () {
            $endpoint = config('phoenix.endpoint', 'https://app.phoenix.arize.com/v1/traces');
            $project = config('phoenix.project', config('app.name', 'laravel'));
            $timeout = (float) config('phoenix.timeout', 5.0);

            $headers = ['x-service-name' => $project];

            if ($apiKey = config('phoenix.api_key')) {
                $headers['api_key'] = $apiKey;
            }

            $exporter = new SpanExporter(
                (new OtlpHttpTransportFactory)->create(
                    endpoint: $endpoint,
                    contentType: ContentTypes::JSON,
                    headers: $headers,
                    timeout: $timeout,
                )
            );

            return new TracerProvider(
                spanProcessors: [new BatchSpanProcessor($exporter, Clock::getDefault())],
                sampler: new AlwaysOnSampler,
                resource: ResourceInfo::create(Attributes::create([
                    'service.name' => $project,
                    'telemetry.sdk.name' => 'laravel-ai',
                    'telemetry.sdk.language' => 'php',
                ])),
            );
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/phoenix.php' => config_path('phoenix.php'),
            ], ['phoenix', 'phoenix-config']);
        }
    }
}
