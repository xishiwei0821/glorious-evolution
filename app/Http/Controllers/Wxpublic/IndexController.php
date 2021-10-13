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
        $result = WxAccess::verifyCode();
        if (!$result) {
            var_dump(false);
        }

        $postXml = $this->request->all();

        return $result;
    }
}
