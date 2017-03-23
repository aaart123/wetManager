<?php
namespace Auth\Controller;

use Think\Controller;

class RegisterController extends Controller
{
    public function __construct()
    {
        parent:: __construct();
    }
    
    /**
     * 注册用户
     * @param array
     */
    public function registered($data)
    {
        return $this->userModel->addData($data);
    }
    
}