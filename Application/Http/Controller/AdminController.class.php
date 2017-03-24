<?php
namespace Http\Controller;

use Http\Controller\AuthController;

class AdminController extends AuthController
{
    
    public function __construct()
    {
        parent::__construct();
    }

    public function test()
    {
        $data = D('Base/publicKey')->getKeys($this->publicId, 'text');
        var_dump($data);
        echo '<hr/>';
        $datas = D('Base/publicKey')->getKeyStrategy($this->publicId, '关键字', 'text');
        var_dump($datas);
    }


/**-------------------------------------------事件列表--------------------------------------------------------*/

    /**
     * 获取自动回复
     * http://www.koudaidaxue.com/index.php/http/admin/getReply
     */
    public function getReply()
    {
        if ($replys = $this->keyActivity->getReply($this->publicId))
        {
            echo json_encode(array(
                'errcode' => 0,
                'errmsg' => $replys
            ));exit;
        } else {
            echo json_encode(array(
                'errcode' => 40001,
                'errmsg' => '失败'
            ));exit;
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
                'errmsg' => '同步成功'
            ]);exit;
        } else {
            echo json_encode(array(
                'errcode' => 40001,
                'errmsg' => '网络超时'
            ));exit;
        }
    }

    /**
     * 获取粉丝列表
     * http://www.koudaidaxue.com/index.php/http/admin/getFansList
     */
    public function getFansList()
    {
        if ($data = $this->fansActivity->getFansList($this->publicId)) {
            echo json_encode(array(
                'errcode' => 0,
                'errmsg' => $data
            ));exit;
        } else {
            echo json_encode(array(
                'errcode' => 40001,
                'errmsg' => '网络超时'
            ));exit;
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
                'errmsg' => '回复文本'
            ];
        if ($eventId = $this->msgActivity->addEvent($this->publicId, $eventMsg)) {
            echo json_encode([
                'errcode' => 0,
                'errmsg' => $eventId
            ]);
            exit;
        } else {
            echo json_encode([
                'errcode' => 40001,
                'errmsg' => '添加失败'
            ]);
            exit;
        }
    }

/** --------------------------------------------消息回复-------------------------------------------------*/


    /**
     * 添加文本自动回复
     * http://www.koudaidaxue.com/index.php/http/admin/addText
     */
    public function addText()
    {
        $keyMsg = I('post.');
        // $keyMsg = [
        //    'key' => '关键字',
        //    'msg' => '回复文本',
        // ];

        if ($keyId = $this->msgActivity->addText($this->publicId, $keyMsg)) {
            echo json_encode(array(
                'errcode' => 0,
                'errmsg' => $keyId
            ));exit;
        } else {
            echo json_encode(array(
                'errcode' => 40001,
                'errmsg' => '添加失败'
            ));exit;
        }
    }

    public function getText()
    {
        // 获取回复关键字
            // http://www.koudaidaxue.com/index.php/http/admin/getText
        $keys = $this->keyActivity->getReply($this->publicId);
        echo json_encode([
            'errcode' => 0,
            'errmsg' => $keys
        ]);
        exit;
    }

    public function deleteText($keyId)
    {
        // 删除回复关键字
            // http://www.koudaidaxue.com/index.php/http/admin/deleteText?keyId=33
        if ($this->msgActivity->deleteText($keyId)) {
            echo json_encode([
                'errcode' => 0,
                'errmsg' => '成功'
            ]);exit;
        } else {
            echo json_encode([
                'errcode' => 92393,
                'errmsg' => '失败'
            ]);exit;
        }
    }

    public function updateKey()
    {
        $data = I('post.');
        // 修改回复关键字
            // http://www.koudaidaxue.com/index.php/http/admin/updateKey
            // $data = [
            //     'keyId' => 36,
            //     'keyword' => '更改关键字',
            //     'isEqual' => 1,
            //     'msg' => '更改后的回复',
            //     'type' => 'text'
            // ];
        if ($this->msgActivity->updateKey($data)) {
            echo json_encode([
                'errcode' => 0,
                'errmsg' => '成功'
            ]);exit;
        } else {
            echo json_encode([
                'errcode' => 3445,
                'errmsg' => '失败'
            ]);
            exit;
        }
    }

/**--------------------------------------------应用处理-------------------------------------------------*/
    /**
     * 获取上架的应用列表✅
     * http://www.koudaidaxue.com/index.php/http/admin/getAppList
     */
    public function getAppList()
    {
        $appList = $this->appActivity->getAppList(array('putaway'=>'1'));
        echo json_encode(array(
            'errcode' => 0,
            'errmsg' => $appList
        ));exit;
    }

    /**
     * 获取应用信息✅
     * http://www.koudaidaxue.com/index.php/http/admin/getAppData?appId=1
     */
    public function getAppData()
    {
    //    $_POST = array(
    //        'appId'=>1,
    //    );
        $appId = I('get.appId');
        $appData = $this->appActivity->getAppData($appId);
        $appData['isOpen'] = 0;
        $appData['keywords'] = $this->appActivity->getAppKeywords($this->publicId,$appId);
        $this->appActivity->isOpen($this->publicId, $appId) && $appData['isOpen'] = 1;
        echo json_encode(array(
            'errcode' => 0,
            'errmsg' => $appData
        ));exit;
    }

    /**
     * 获取已开启应用列表✅
     * http://www.koudaidaxue.com/index.php/http/admin/getOpenAppList
     */
    public function getOpenAppList()
    {
        $appList = $this->appActivity->getAppListByPublic($this->publicId);
        foreach($appList as &$value){
            if( $this->appActivity->isOpen($this->publicId, $value['app_id']) )
            {
                $value['is_open'] = 1;
            }else{
                $value['is_open'] = 0;
            }
        }
        echo json_encode(array(
            'errcode' => 0,
            'errmsg' => $appList
        ));exit;
    }

    /**
     * 开启应用✅
     * http://www.koudaidaxue.com/index.php/http/admin/openApp
     */
    public function openApp()
    {
/*        $_POST = array(
            'appId'=>1,
            'keywords'=>['关键字开启1','关键字开启2'],
        );*/
        if(empty($_POST) || !isset($_POST))
        {
            echo json_encode(array(
                'errcode'=>10001,
                'errmsg'=>'数据为空！'
            ));exit;
        }
        $appId = I('post.appId');
        $keywords = I('post.keywords');

        if (!$this->appActivity->isHasKeyword($this->publicId, $keywords)) {
            echo json_encode([
                'errcode' => 40001,
                'errmsg' => '关键字冲突！'
            ]);exit;
        }
        if ( $result = $this->appActivity->addAppConfig($this->publicId, $keywords, $appId) )
        {
            echo json_encode([
                'errcode' => 0,
                'errmsg' => '开启成功！',
            ]);exit;
        } else {
            echo json_encode([
                'errcode' => 40002,
                'errmsg' => '开启失败！'
            ]);exit;
        }
    }

    /**
     * 应用触发
     * http://www.koudaidaxue.com/index.php/Http/Admin/triggerApp
     */
    public function triggerApp()
    {

    }

    /**
     * 获取公众号对此应用设置的关键词
     * http://www.koudaidaxue.com/index.php/Http/Admin/getAppKeywords?appId=1
     */
    public function getAppKeywords()
    {

        if(empty($_GET['appId']) || !isset($_GET['appId']))
        {
            echo json_encode(array(
                'errcode'=>10001,
                'errmsg'=>'参数为空！',
            ));exit;
        }

        $appId = I('get.appId');
        $keywords = $this->appActivity->getAppKeywords($this->publicId,$appId);
        echo json_encode(array(
            'errcode'=>0,
            'errmsg'=>$keywords
        ));exit;
    }


    /**
     * 更新应用配置✅
     * http://www.koudaidaxue.com/index.php/http/admin/updateAppConfig
     * $keywords = [ '关键词1', '关键词2'，...];
     */
    public function updateAppConfig()
    {
        // $_POST = array(
        //     'appId'=>1,
        //     'keywords'=>['关键词111','关键词222'],
        // );



        if(empty($_POST) || !isset($_POST))
        {
            echo json_encode(array(
                'errcode'=>10001,
                'errmsg'=>'参数为空！',
            ));exit;
        }


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
                        'errmsg'=>'修改成功！'
                    ));exit;
                }else{
                    M()->rollback();
                    echo json_encode(array(
                        'errcode'=>40003,
                        'errmsg'=>'开启失败！',
                    ));eixt;
                }
            }else{
                M()->rollback();
                echo json_encode(array(
                    'errcode'=>40001,
                    'errmsg'=>'关键词冲突！',
                ));exit;
            }
        }else{
            M()->rollback();
            echo json_encode(array(
                'errcode'=>40002,
                'errmsg'=>'关闭失败！',
            ));exit;
        }
    }

    /**
     * 关闭应用✅
     * http://www.koudaidaxue.com/index.php/http/admin/closeApp?appId=1
     */
    public function closeApp()
    {

        if(empty($_GET['appId']) || !isset($_GET['appId']))
        {
            echo json_encode(array(
                'errcode'=>10001,
                'errmsg'=>'参数为空！',
            ));exit;
        }


        $appId = I('get.appId');
        if ($result = $this->appActivity->closeApp($this->publicId, $appId))
        {
            echo json_encode(array(
                'errcode' => 0,
                'errmsg' => '关闭成功！'
            ));exit;
        } else {
            echo json_encode(array(
                'errcode' => 40001,
                'errmsg' => '关闭失败！'
            ));exit;
        }
    }


    /**
     * 获取应用管理页✅
     * http://www.koudaidaxue.com/index.php/Http/Admin/getInterface?appId=1
     */
    public function getInterface()
    {

        if(empty($_GET['appId']) || !isset($_GET['appId']))
        {
            echo json_encode(array(
                'errcode'=>10001,
                'errmsg'=>'参数为空！',
            ));exit;
        }


        $appId = I('get.appId');
        if($this->appActivity->isOpen($this->publicId, $appId))
        {

            $param_array = array(
                'media_id'  =>  $this->publicId,
                'platform'  =>  'koudaidaxue',
                'timestamp' =>  time(),
                'nonce_str' =>  getRandomStr(),
            );
            $api_secret = M('kdgx_plat_app')->where(array('app_id'=>$appId))->getField('api_secret');
            $param_array['sign'] = $this->calSign($param_array, $api_secret);
            $data = $this->appActivity->getAppData($appId);
            $url = $data['url'].'?type=config';
            foreach ($param_array as $key=>$value) {
                $url .= '&'.$key.'='.$value;
            }
            echo json_encode(array(
                'errcode'=>0,
                'errmsg'=>$url
            ));exit;
        }else{
            echo json_encode(array(
                'errcode'=>40001,
                'errmsg'=>'应用未开启！'
            ));exit;
        }


    }


    /**
     * 计算签名
     * @param array $param_array
     */
    private function calSign($param_array, $api_secret) {
        $names = array_keys($param_array);
        sort($names, SORT_STRING);

        $item_array = array();
        foreach ($names as $name) {
            $item_array[] = "{$name}={$param_array[$name]}";
        }

        $str = implode('&', $item_array) . '&key=' . $api_secret;
        return strtoupper(md5($str));
    }


}