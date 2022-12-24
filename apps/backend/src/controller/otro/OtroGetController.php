<?php

namespace Tramity\Apps\Backend\controller\otro;

use Symfony\Component\HttpFoundation\JsonResponse;
use Tramity\shared\domain\RandomNumberGenerator;

class OtroGetController
{
    private RandomNumberGenerator $generator;

    public function __construct(RandomNumberGenerator $generator)
    {
        $this->generator = $generator;

    }

    public function __invoke(): JsonResponse
    {
        return new JsonResponse([
            'status' => 'ok',
            'number' => $this->generator->generate()
        ]);
    }

}