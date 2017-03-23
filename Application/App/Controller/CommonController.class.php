<?php
namespace App\Controller;

use Base\Controller\WeichatApiController;

class CommonController extends WeichatApiController
{
    private $appModel;
    private $publicKeyModel;

    private $apiSecret;
    public function __construct()
    {
        parent:: __construct();
        $this->appModel = D('Base/App');
        $this->publicKeyModel = D('Base/PublicKey');
        $this->checkSign();
    }

    /**
     * 获取公众号基本信息
     */
    public function getMediaInfo()
    {
        // $post = file_get_contents('php://input');
        $post = I('post.');
        if ($appData = $this->checkSign($post)) {
            $authorizer_appid = $this->getAuthorizerAppid($post['media_id']);
            $publicInfo = $this->getPublicInfo($authorizer_appid);
            echo json_encode($publicInfo);
            exit;
        }
    }

    /**
     * 获取应用关键字
     */
    public function getMediaKeywords()
    {
        $post = I('post.');
        if ($appData = $this->checkSign($post)) {
            $keys = $this->publicKeyModel->getKeysApp($post['media_id'], $appData['appId'], 'app');
            $keyword = '';
            foreach ($keys as $key) {
                $keyword .= $key['keyword'].',';
            }
            $keyword = rtrim($keyword, ',');
            $appData['keyword'] = $keyword;
            echo json_encode($appData);
            exit;
        }
    }

    /**
     * 校验签名
     */
    private function checkSign($post)
    {
        $sign = $post['sign'];
        unset($post['sign']);
        $appData = $this->appModel->getDataSecret($post['api_key']);

        $this->apiSecret = $appData['secret'];
        $calSign = $this->calSign($post);
        if ($calSign == $sign) {
            $interval = time() - $calSign['timestamp'];

            if ($interval >= 0 && $interval < 10) {
                return $appData;
            } else {
                echo json_encode([
                    'errcode' => 5003,
                    'errmsg' => '请求接口失败,可能是重放攻击'
                ]);
                exit;
            }
        } else {
            echo json_encode([
                'errcode' => 5004,
                'errmsg' => '签名错误'
            ]);
            exit;
        }
    }

    /**
     * 生成TOKEN
     */
    private function genToken($media_id)
    {
        $key = 'SECRET'; //自己定义SECRET值
        $string = strlen($media_id) . $media_id;

        $b = 64;
        if (strlen($key) > $b) {
            $key = pack("H*", md5($key));
        }

        $key = str_pad($key, $b, chr(0x00));
        $ipad = str_pad('', $b, chr(0x36));
        $opad = str_pad('', $b, chr(0x5c));
        $k_ipad = $key ^ $ipad ;
        $k_opad = $key ^ $opad;

        return md5($k_opad . pack("H*", md5($k_ipad . $string)));
    }

    /**
     * 计算签名
     * @param array $param_array
     */
    private function calSign($param_array)
    {
        $names = array_keys($param_array);
        sort($names, SORT_STRING);
        
        $item_array = array();
        foreach ($names as $name) {
            $item_array[] = "{$name}={$param_array[$name]}";
        }

        $str = implode('&', $item_array) . '&key=' . $this->apiSecret;
        return strtoupper(md5($str));
    }
}
