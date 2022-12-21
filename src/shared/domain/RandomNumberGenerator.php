<?php

namespace Tramity\shared\domain;

interface RandomNumberGenerator
{
    public function generate(): int;
}