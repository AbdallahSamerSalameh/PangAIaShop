<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TestController extends Controller
{
    /**
     * Simple test method to verify routing
     */
    public function test()
    {
        return "Admin route test working!";
    }
}
