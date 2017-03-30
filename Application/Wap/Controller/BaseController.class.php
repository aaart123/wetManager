<?php

namespace Wap\Controller;

use Think\Controller;



class BaseController extends Controller
{

    public function __construct()
    {
        parent::__construct();
        if(empty($_SESSION['plat_user_id']))
        {

            $url = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
            $login = A('login');
            $login->authorization($url);
        }

    }

}