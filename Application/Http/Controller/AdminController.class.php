<?php
namespace Http\Controller;

use Http\Controller\AuthController;

class AdminController extends AuthController
{
    
    public function __construct()
    {
        parent::__construct();
<<<<<<< HEAD
=======
        $this->publicId = session('plat_public_id');
        $this->publicId = 'gh_c75321282c18';
        $this->msgActivity = A('Message/Message');
        $this->appActivity = A('App/App');
        $this->keyActivity = A('Base/Key');
        $this->fansActivity = A('Base/fans');
        $this->receiveActivity = A('Base/Receive');
>>>>>>> ceec921bc1e6bc88ee6cd6f194b24a2cdbe918de
    }

    public function test()
    {
        $param = array(
            'ToUserName'=>'111',
            'FromUserName'=>2,''
        );
        $data = arr2Xml($param);

        echo $data;
    }


/**-------------------------------------------事件列表--------------------------------------------------------*/

    /**
     * 获取自动回复
     * http://www.koudaidaxue.com/index.php/http/admin/getReply
     */
    public function getReply()
    {
        if ($replys = $this->keyActivity->getReply($this->publicId)) {
            echo json_encode([
                'errcode' => 0,
                'msg' => $replys
            ]);exit;
        } else {
            echo json_encode([
                'errcode' => 40001,
                'msg' => '失败'
            ]);exit;
        }
    }



/**-------------------------------------------粉丝管理-------------------------------------------------*/

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
        $keyMsg = I('post.');
        // $keyMsg = array(
        //    'key' => '关键字',
        //    'msg' => '回复文本',
        // );

        if ($keyId = $this->msgActivity->addText($this->publicId, $keyMsg)) {
            echo json_encode(array(
                'errcode' => 0,
                'msg' => $keyId
            ));exit;
        } else {
            echo json_encode(array(
                'errcode' => 40001,
                'msg' => '添加失败'
            ));exit;
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
     * 获取已开启应用列表✅
     * http://www.koudaidaxue.com/index.php/http/admin/getOpenAppList
     */
    public function getOpenAppList()
    {

        $appList = $this->appActivity->getAppListByPublic($this->publicId);
        foreach($appList as &$value){
            if( $this->appActivity->isOpen($this->publicId,$value['app_id']) )
            {
                $value['is_open'] = 1;
            }else{
                $value['is_open'] = 0;
            }
        }
        echo json_encode([
            'errcode' => 0,
            'msg' => $appList
        ]);exit;
    }

    /**
     * 开启应用✅
     * http://www.koudaidaxue.com/index.php/http/admin/openApp
     */
    public function openApp()
    {
        $_POST = array(
            'appId'=>1,
            'keywords'=>['关键字开启1','关键字开启2'],
        );
        $appId = I('post.appId');
        $keywords = I('post.keywords');

        if (!$this->appActivity->isHasKeyword($this->publicId, $keywords)) {
            echo json_encode([
                'errcode' => 40001,
                'msg' => '关键字冲突！'
            ]);exit;
        }
        if ( $result = $this->appActivity->addAppConfig($this->publicId, $keywords, $appId) )
        {
            echo json_encode([
                'errcode' => 0,
                'msg' => '开启成功！',
            ]);exit;
        } else {
            echo json_encode([
                'errcode' => 40001,
                'msg' => '开启失败！'
            ]);exit;
        }
    }

    /**
     * 更新应用配置✅
     * http://www.koudaidaxue.com/index.php/http/admin/updateAppConfig?appId=1
     * $keywords = [ '关键词1', '关键词2'，...];
     */
    public function updateAppConfig()
    {
        $_POST = array(
            'appId'=>1,
            'keywords'=>['关键词111','关键词222'],
        );
        $keywords = I('post.keywords');
        $appId = I('post.appId');
        M()->startTrans();
        if($this->appActivity->closeApp($this->publicId,$appId))//先删除
        {
            if($this->appActivity->isHasKeyword($this->publicId, $keywords))//判断是否关键字冲突
            {
                if($this->appActivity->addAppConfig($this->publicId, $keywords, $appId))//添加应用
                {
                    M()->commit();
                    echo json_encode(array(
                        'errcode'=>0,
                        'msg'=>'修改成功！'
                    ));exit;
                }else{
                    M()->rollback();
                    echo json_encode(array(
                        'errcode'=>40003,
                        'msg'=>'开启失败！',
                    ));eixt;
                }
            }else{
                M()->rollback();
                echo json_encode(array(
                    'errcode'=>40001,
                    'msg'=>'关键词冲突！',
                ));exit;
            }
        }else{
            M()->rollback();
            echo json_encode(array(
                'errcode'=>40002,
                'msg'=>'关闭失败！',
            ));exit;
        }
    }

    /**
     * 关闭应用✅
     * http://www.koudaidaxue.com/index.php/http/admin/closeApp?appId=1
     */
    public function closeApp()
    {
        $_POST = array(
            'appId'=>1
        );
        $appId = I('post.appId');
        if ($result = $this->appActivity->closeApp($this->publicId, $appId)) {
            echo json_encode([
                'errcode' => 0,
                'msg' => '关闭成功！'
            ]);exit;
        } else {
            echo json_encode([
                'errcode' => 40001,
                'msg' => '关闭失败！'
            ]);exit;
        }
    }

}