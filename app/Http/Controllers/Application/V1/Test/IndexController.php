<?php

namespace App\Http\Controllers\Application\V1\Test;

use App\Http\Controllers\Application\BaseController;

class IndexController extends BaseController
{
    public function index()
    {
        return self::checked_update();
    }
}
