<?php

namespace HJUGroup\ConvertKit;

use Illuminate\Support\Facades\Facade;

class ConvertKitFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'convertkit';
    }
}
