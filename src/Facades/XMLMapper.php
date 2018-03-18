<?php
namespace Edujugon\XMLMapper\Facades;

use Illuminate\Support\Facades\Facade;
use Edujugon\XMLMapper\XMLMapper as XMLMapperClass;

class XMLMapper extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return XMLMapperClass::class;
    }


}