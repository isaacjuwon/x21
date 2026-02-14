<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller as BaseController;
use App\Concerns\ApiResponse;

abstract class ApiController extends BaseController
{
    use ApiResponse;
}
