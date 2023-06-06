<?php

namespace Mrfrost\GoogleApi\ApiService\Extras;

interface GapiExtras
{
    // For initialization before access the simple run method
    public function init(array $options = []): self;

    // For execute main features of extras
    public function run(array $options = []);
}