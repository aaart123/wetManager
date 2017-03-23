<?php
namespace Home\Controller;

use Think\Controller;

class IndexController extends Controller
{
    public function __construct()
    {
        parent:: __construct();
    }

    public function Index()
    {
        $this->display();
    }
    
}