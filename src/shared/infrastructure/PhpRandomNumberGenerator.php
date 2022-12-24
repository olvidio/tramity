<?php

namespace Tramity\shared\infrastructure;


use Tramity\shared\domain\RandomNumberGenerator;

class PhpRandomNumberGenerator implements RandomNumberGenerator
{
    public function generate(): int
    {
        return random_int(1, 5);
    }

}