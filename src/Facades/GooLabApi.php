<?php

namespace OddOnes\GooLabApi\Facades;

use Illuminate\Support\Facades\Facade;

class GooLabApi extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'goolabapi';
    }
}
