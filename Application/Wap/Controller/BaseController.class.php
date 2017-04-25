<?php

namespace Wap\Controller;

use Wap\Controller\LoginController;

class BaseController extends LoginController
{

    public function __construct()
    {
        parent::__construct();
        //授权公众号
        $userId = $_SESSION['plat_user_id'];
        $data = D('Wap/PublicUser')->getPublicInfo($userId);
        if( $data = D('Wap/PublicUser')->getPublicInfo($userId) )
        {
            $login_public = D('Wap/conf')->where(array('user_id'=>$userId))->getField('login_public');
            if($login_public)
            {
                $_SESSION['plat_public_id'] = $login_public;
            }else{
                D('Wap/conf')->where(array('user_id'=>$userId))->setField('login_public',$data[0]['user_name']);
                $_SESSION['plat_public_id'] = $data[0]['user_name'];
            }

        }else{
            header('Location:http://www.koudaidaxue.com/index.php/wap/Login/binding');exit;
        }
    }

}