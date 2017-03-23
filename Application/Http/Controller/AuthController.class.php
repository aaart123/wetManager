<?php
namespace Http\Controller;

use Think\Controller;

class AuthController extends Controller
{

    protected $msgActivity;
    protected $appActivity;
    protected $keyActivity;
    
    protected $publicId;

    public function __construct()
    {
        session('plat_public_id', 'gh_19fb1bed539e');
        if (!session('plat_public_id')) {
            echo "<script>alert('用户尚未登陆!');</script>";
            exit;
        }
        parent::__construct();
        $this->publicId = session('plat_public_id');
        $this->msgActivity = A('Message/Message');
        $this->appActivity = A('App/App');
        $this->keyActivity = A('Message/Key');
    }
}
