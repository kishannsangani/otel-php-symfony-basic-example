<?php

namespace App\Controller;

use OpenTelemetry\SDK\Trace\TracerProvider;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HelloController extends AbstractController
{
    /**
     * @Route("/hello", name="hello")
     */
    public function index(): Response
    {
        date_default_timezone_set('Asia/Kolkata');
        $date = date('d/m/Y h:i:s a', time());

        global $rootSpan;
        if ($rootSpan) {
            /** @var Span $rootSpan */
            $rootSpan->setAttribute('foo', 'bar');
            $rootSpan->setAttribute('Kishan', 'Sangani');
            $rootSpan->setAttribute('foo', 'bar1');
            $rootSpan->updateName('HelloController\\index dated ' . $date);

            $tracer = TracerProvider::getDefaultTracer();
            $childSpan = $tracer->spanBuilder('Child span')->startSpan();
            try {
                throw new \Exception('Exception Example', 507);
            } catch (\Exception $exception) {
                // echo "HelloController\\index: Exception: " . $exception->getCode() . "(" . $exception->getMessage() . ")" . PHP_EOL;
                $childSpan->setStatus($exception->getCode(), $exception->getMessage());
            }
            $childSpan->activate();
            $grandchildSpan = $tracer->spanBuilder('Grandchild span')->startSpan();
            $grandchildSpan->setAttribute("Generation", 3);
            $grandchildSpan->end();
            $childSpan->end();
        }

        return new Response('Hello from the other side! - Adele');
    }
}