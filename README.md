# Laravel AI Phoenix

<p>
<a href="https://packagist.org/packages/vinitkadam/laravel-ai-phoenix"><img src="https://img.shields.io/packagist/dt/vinitkadam/laravel-ai-phoenix" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/vinitkadam/laravel-ai-phoenix"><img src="https://img.shields.io/packagist/v/vinitkadam/laravel-ai-phoenix" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/vinitkadam/laravel-ai-phoenix"><img src="https://img.shields.io/packagist/l/vinitkadam/laravel-ai-phoenix" alt="License"></a>
</p>

## Introduction

Laravel AI Phoenix wires [Arize Phoenix](https://phoenix.arize.com) as the OpenTelemetry backend for [laravel-ai-telemetry](https://github.com/vinitkadam/laravel-ai-telemetry). It registers a Phoenix-backed `TracerProvider` via `Globals::registerInitializer`, so every `gen_ai.*` span emitted by the telemetry package is exported to Phoenix automatically — no manual OTel setup required.

## Requirements

- PHP 8.3+
- Laravel 12+
- [vinitkadam/laravel-ai-telemetry](https://github.com/vinitkadam/laravel-ai-telemetry) ^1.0

## Installation

```bash
composer require vinitkadam/laravel-ai-phoenix
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag=phoenix-config
```

## Configuration

Add the following to your `.env`:

```env
AI_TELEMETRY_ENABLED=true
AI_TELEMETRY_DRIVER=otel

# Arize-hosted Phoenix
PHOENIX_ENDPOINT=https://app.phoenix.arize.com/v1/traces
PHOENIX_API_KEY=your-api-key
PHOENIX_PROJECT=my-laravel-app

# Or self-hosted Phoenix
# PHOENIX_ENDPOINT=http://localhost:6006/v1/traces
```

The `PHOENIX_PROJECT` groups your traces under a named project in the Phoenix UI. It defaults to your application's `APP_NAME`.

### Service Provider Order

`PhoenixServiceProvider` must register before `AiTelemetryServiceProvider` so the `TracerProvider` is in place when the `otel` driver first resolves. Laravel's auto-discovery handles this automatically. If you have manually ordered providers, ensure Phoenix comes first.

## How It Works

On boot, the service provider calls `Globals::registerInitializer(...)` with a closure that builds a `TracerProvider` backed by an OTLP/HTTP exporter pointed at your Phoenix endpoint. When `laravel-ai-telemetry`'s `otel` driver emits a span, it calls `Globals::tracerProvider()`, which returns this Phoenix-backed provider. Spans are batched and exported asynchronously.

## License

Laravel AI Phoenix is open-sourced software licensed under the [MIT license](LICENSE.md).
