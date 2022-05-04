<?php

use App\Kernel;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;

use OpenTelemetry\Contrib\Jaeger\Exporter as JaegerExporter;
use OpenTelemetry\Contrib\Zipkin\Exporter as ZipkinExporter;
use OpenTelemetry\SDK\Common\Time\ClockFactory;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessor\MultiSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

$jaegerExporter = new JaegerExporter(
    'Hello World Web Server Jaeger',
    'http://localhost:9412/api/v2/spans',
    new Client(),
    new HttpFactory(),
    new HttpFactory()
);

$zipkinExporter = new ZipkinExporter(
    'Hello World Web Server Zipkin',
    'http://localhost:9411/api/v2/spans',
    new Client(),
    new HttpFactory(),
    new HttpFactory()
);

$tracer = (new TracerProvider(
                              new MultiSpanProcessor(
                                                    new SimpleSpanProcessor($jaegerExporter),
                                                    new BatchSpanProcessor($zipkinExporter, ClockFactory::getDefault())
                                                    )
                             )
          )
          ->getTracer('io.opentelemetry.contrib.php');

$request = Request::createFromGlobals();
$rootSpan = $tracer->spanBuilder($request->getUri())->startSpan();
$rootScope = $rootSpan->activate();


// $rootScope->detach();
// $rootSpan->end();

return function (array $context) {
    // $kernel = new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
    // $kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
    // $response = $kernel->handle($request);
    // $response->send();
    // $kernel->terminate($request, $response);

    // return new Response('Welcome to Symfony!');
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
