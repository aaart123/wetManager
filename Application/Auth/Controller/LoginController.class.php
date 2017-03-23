<?php
namespace User\Controller;

use Base\Controller\BaseController;

/**
 * 登录模块
 */

class LoginController extends BaseController
{
    protected $userModel;
    public function __construct()
    {
        parent:: __construct();
        $this->userModel = D('user');
    }

    /**
     * 用户登录
     * @param array
     * @return boolean
     */
    public function login($data)
    {
        if(empty($data['phone']) || empty('password')) {
            return false;
        }
        $data['password'] = md5($data['password']);
        if($this->userModel->checkLogin($data)) {
            return true;
        }else {
            return false;
        }
    }
    
}