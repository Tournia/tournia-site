<?php

namespace TS\AccountBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class TSAccountBundle extends Bundle
{
    public function getParent() {
        return 'FOSUserBundle';
    }
}
