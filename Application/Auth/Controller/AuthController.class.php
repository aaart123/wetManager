<?php
namespace Auth\Controller;

use Think\Controller;

class AuthController extends Controller
{
    private $userModel;
    public function __construct()
    {
        parent:: __construct();
        $this->userModel = D('user');
    }

}