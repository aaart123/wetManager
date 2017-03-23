<?php
namespace Http\Controller;

use Http\Controller\BaseController;

class AdminController extends BaseController
{
    private $msgActivity;
    private $appActivity;
    private $keyActivity;
    private $receiveActivity;
    private $fansActivity;
    private $publicId;
    
    public function __construct()
    {
        parent::__construct();
        $this->publicId = session('plat_public_id');
        $this->publicId = 'gh_c75321282c18';
        $this->msgActivity = A('Base/Msg');
        $this->appActivity = A('App/App');
        $this->keyActivity = A('Base/Key');
        $this->fansActivity = A('Base/fans');
        $this->receiveActivity = A('Base/Receive');
    }

    public function test()
    {
        $publicId ='gh_c75321282c18';
        $thisObj = A('Base/Receive');

        $appid = $thisObj->getAuthorizerAppid($publicId);
        $userOpenidModel = D('userOpenid');


        var_dump($appid);
        var_dump($userOpenidModel);
    }

/**--------------------------------------------粉丝管理-------------------------------------------------*/
    /**
     * 同步粉丝
     * http://www.koudaidaxue.com/index.php/http/admin/syncFans
     */
    public function syncFans()
    {
        if ($this->receiveActivity->getFansList($this->publicId)) {
            echo json_encode([
                'errcode' => 0,
                'msg' => '同步成功'
            ]);exit;
        } else {
            echo json_encode([
                'errcode' => 40001,
                'msg' => '网络超时'
            ]);exit;
        }
    }

    /**
     * 获取粉丝列表
     * http://www.koudaidaxue.com/index.php/http/admin/getFansList
     */
    public function getFansList()
    {
        if ($data = $this->fansActivity->getFansList($this->publicId)) {
            echo json_encode([
                'errcode' => 0,
                'msg' => $data
            ]);
            exit;
        } else {
            echo json_encode([
                'errcode' => 40001,
                'msg' => '网络超时'
            ]);
            exit;
        }
    }


/**--------------------------------------------事件回复-------------------------------------------------*/

    /**
     * 添加事件自动回复
     * http://www.koudaidaxue.com/index.php/http/admin/addEvent
     */
    public function addEvent()
    {
        $eventMsg = I('post.');
            $eventMsg = [
                'event' => 'subscribe',
                // 'eventKey' => '1'   #事件值可为空
                'msg' => '回复文本'
            ];
        if ($eventId = $this->msgActivity->addEvent($this->publicId, $eventMsg)) {
            echo json_encode([
                'errcode' => 0,
                'msg' => $eventId
            ]);
            exit;
        } else {
            echo json_encode([
                'errcode' => 40001,
                'msg' => '添加失败'
            ]);
            exit;
        }
    }




/**--------------------------------------------消息回复-------------------------------------------------*/


    /**
     * 添加文本自动回复
     * http://www.koudaidaxue.com/index.php/http/admin/addText
     */
    public function addText()
    {
        //示例  $_POST = array(
        //    'key' => '关键字',
        //    'msg' => '回复文本',
        //  );
        $keyMsg = I('post.');

        if ($keyId = $this->msgActivity->addText($this->publicId, $keyMsg)) {
            echo json_encode([
            'errcode' => 0,
            'msg' => $keyId
            ]);
            exit;
        } else {
            echo json_encode([
            'errcode' => 40001,
            'msg' => '添加失败'
            ]);
            exit;
        }
    }

    /**
     * 获取自动回复
     * http://www.koudaidaxue.com/index.php/http/admin/getReply
     */
    public function getReply()
    {
        // 获取自动回复
            // http://www.koudaidaxue.com/index.php/http/admin/getReply
        if ($replys = $this->keyActivity->getReply($this->publicId)) {
            echo json_encode([
                'errcode' => 0,
                'msg' => $replys
            ]);
            exit;
        } else {
            echo json_encode([
                'errcode' => 40001,
                'msg' => '失败'
            ]);
            exit;
        }
    }




/**--------------------------------------------应用处理-------------------------------------------------*/
    /**
     * 获取应用列表✅
     * http://www.koudaidaxue.com/index.php/http/admin/getAppList
     */
    public function getAppList()
    {

        $appList = $this->appActivity->getAppList();
        echo json_encode([
            'errcode' => 0,
            'msg' => $appList
        ]);
        exit;
    }

    /**
     * 获取应用信息✅
     * http://www.koudaidaxue.com/index.php/http/admin/getAppData?appId=1
     */
    public function getAppData()
    {

        $appId = I('get.appId');
        $appData = $this->appActivity->getAppData($appId);
        $appData['isOpen'] = 0;
        $this->appActivity->isOpen($this->publicId, $appId) && $appData['isOpen'] = 1;
        echo json_encode([
            'errcode' => 0,
            'msg' => $appData
        ]);
        exit;
    }

    /**
     * 获取已开启应用列表
     * http://www.koudaidaxue.com/index.php/http/admin/getOpenAppList
     */
    public function getOpenAppList()
    {

        $appList = $this->appActivity->getAppListByPublic($this->publicId);
        echo json_encode([
            'errcode' => 0,
            'msg' => $appList
        ]);
        exit;
    }

    /**
     * 开启应用
     * http://www.koudaidaxue.com/index.php/http/admin/openApp?appId=1
     */
    public function openApp()
    {
        $_POST = array(
            'appId'=>1,
            'keywords'=>['关键字一','关键字二'],
        );
        $appId = I('post.appId');
        $keywords = I('post.keywords');

        if (!$this->appActivity->isHasKeyword($this->publicId, $keywords)) {
            echo json_encode([
                'errcode' => 40001,
                'msg' => '关键字冲突'
            ]);exit;
        }
        if ($result = $this->appActivity->addAppConfig($this->publicId, $keywords, $appId)) {
            echo json_encode([
                'errcode' => 0,
                'msg' => $appData
            ]);
            exit;
        } else {
            echo json_encode([
                'errcode' => 40001,
                'msg' => '失败'
            ]);
            exit;
        }
    }

    /**
     * 更新应用配置
     * http://www.koudaidaxue.com/index.php/http/admin/updateAppConfig?appId=1
     * $keywords = [ '关键词1', '关键词2'，...];
     */
    public function updateAppConfig()
    {
        $keywords = I('post.');
        $appId = I('get.appId');
        if ($result = $this->appActivity->updateAppConfig($this->publicId, $keywords, $appId)) {
            echo json_encode([
                'errcode' => 0,
                'msg' => $result
            ]);
            exit;
        } else {
            echo json_encode([
                'errcode' => 40001,
                'msg' => '失败'
            ]);
            exit;
        }
    }


    /**
     * 关闭应用
     * http://www.koudaidaxue.com/index.php/http/admin/closeApp?appId=1
     */
    public function closeApp()
    {
        $appId = I('get.appId');
        if ($result = $this->appActivity->closeApp($this->publicId, $appId)) {
            echo json_encode([
                'errcode' => 0,
                'msg' => $result
            ]);
            exit;
        } else {
            echo json_encode([
                'errcode' => 40001,
                'msg' => '失败'
            ]);
            exit;
        }
    }
}