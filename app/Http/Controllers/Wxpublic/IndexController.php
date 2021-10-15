<?php

namespace App\Http\Controllers\Wxpublic;

use App\Http\Controllers\Controller;
use App\Services\WxAccess;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function wx_access()
    {
        return WxAccess::verifyCode();
    }
}
