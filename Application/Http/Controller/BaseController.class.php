<?php
namespace Http\Controller;

use Think\Controller;

class BaseController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        if (session('user')) {
            $from = $_SERVER['REQUEST_URI'];
            $from = urlencode($from);
            $url = 'Location:http://'.C('WEB_SITE').'/index.php/User/login/login?from='.$from;
            header($url);
            exit;
        }
    }
}
