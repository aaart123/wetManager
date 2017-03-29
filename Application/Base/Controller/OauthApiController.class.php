<?php
namespace Base\Controller;

use Base\Controller\BaseController;

// 口袋高校第三方开发第三方授权核心类
class OauthApiController extends BaseController
{

    private $receive; // 第三方服务事件接收类
    private $component_appid; // 第三方服务的appid
    private $component_appsecret; // 第三方服务的appsecret
    private $encodingaeskey;    // 第三方服务的ENCODINGAESKEY
    private $token; // 第三方服务的TOKEN
    private $publicModel;
 
    public function __construct()
    {
        parent:: __construct();
        $this->component_appid = C('COMPONENT_APPID');
        $this->component_appsecret = C('COMPONENT_APPSECRET');
        $this->encodingaeskey = C('ENCODINGAESKEY');
        $this->token = C('TOKEN');
        $this->publicModel = D('Base/Public');
    }

    // 获取component_verify_ticket
    private function getComponent_verify_ticket()
    {
        $component_verify_ticket = file_get_contents('component_verify_ticket');
        return $component_verify_ticket;
    }

    // 获取component_access_token
    protected function getComponent_access_aoken()
    {
        $response = file_get_contents('component_access_token');
        $response = json_decode($response);
        if (time()-filemtime('component_access_token')<$response->expires_in) {
            return $response->component_access_token;
        }
        $component_verify_ticket = $this->getComponent_verify_ticket();
        $data = '{
        "component_appid":"'.$this->component_appid.'",  
        "component_appsecret":"'.$this->component_appsecret.'",  
        "component_verify_ticket":"' . $component_verify_ticket . '"}';
        $url = "https://api.weixin.qq.com/cgi-bin/component/api_component_token";
        $response = httpRequest($url, $data);
        file_put_contents('component_access_token', $response);
        $response = json_decode($response);
        return $response->component_access_token;
    }

    // 获取pre_auth_code
    protected function getPre_auth_code()
    {
        $response = file_get_contents('pre_access_token');
        if (time()-filemtime('pre_access_token')>$response->expires_in) {
            $component_access_token = $this->getComponent_access_aoken();
            $url = "https://api.weixin.qq.com/cgi-bin/component/api_create_preauthcode?component_access_token=".$component_access_token;
            $data = '{
            "component_appid": "'.$this->component_appid.'"}';
            $response = httpRequest($url, $data);
            file_put_contents('pre_auth_token', $response);
        }
        $ret = json_decode($response, true);
        return $ret['pre_auth_code'];
    }

    // 获取公众号的接口调用凭据和授权信息authorizer_access_token
    public function getAuths($authorizer_appid)
    {
        $auth_info = M('kdgx_plat_authorizer')->where(array('authorizer_appid'=>$authorizer_appid))->find();
        if ($auth_info['authorizer_access_token_expires_in']<time()) {
            $authorizer_access_token = $this->RefreshAuthToken($authorizer_appid, $auth_info['authorizer_refresh_token']);
        } else {
            $authorizer_access_token = $auth_info['authorizer_access_token'];
        }
        return $authorizer_access_token;
    }

    // 刷新公众号的接口调用凭据
    private function RefreshAuthToken($authorizer_appid, $authorizer_refresh_token)
    {
        $component_access_token = $this->getComponent_access_aoken();
        $url = "https://api.weixin.qq.com/cgi-bin/component/api_authorizer_token?component_access_token={$component_access_token}";
        $data = '{
        "component_appid":"'.C('COMPONENT_APPID').'",
        "authorizer_appid":"'.$authorizer_appid.'",
        "authorizer_refresh_token":"'.$authorizer_refresh_token.'"}';
        $response = json_decode(httpRequest($url, $data));
        if (isset($response->authorizer_refresh_token)) {
            $save = array(
            'authorizer_access_token'=>$response->authorizer_access_token,
            'authorizer_refresh_token'=>$response->authorizer_refresh_token,
            'authorizer_access_token_expires_in'=>time()+$response->expires_in
            );
            M('kdgx_plat_authorizer')->where(array('authorizer_appid'=>$authorizer_appid))->save($save);
        }
        return $response->authorizer_access_token;
    }
    
    // 获取授权方的公众号帐号基本信息
    public function getPublicInfo($authorizer_appid)
    {
        if (!$data = M('kdgx_plat_public')->where(array('authorizer_appid'=>$authorizer_appid))->find()) {
            $component_access_token = $this->getComponent_access_aoken();
            $url = "https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_info?component_access_token={$component_access_token}";
            $data = '{"component_appid":"'.C('COMPONENT_APPID').'","authorizer_appid":"'.$authorizer_appid.'"}';
            $response = json_decode(httpRequest($url, $data), true);
            foreach ($response['authorization_info']['func_info'] as $key => $value) {
                $func_info .= $value['funcscope_category']['id'] .',';
            }
            $func_info = rtrim($func_info, ',');
            $data = array(
                'nick_name' => $response['authorizer_info']['nick_name'],
                'head_img'  => $response['authorizer_info']['head_img'],
                'service_type_info' => $response['authorizer_info']['service_type_info']['id'],
                'verify_type_info'  => $response['authorizer_info']['verify_type_info']['id'],
                'user_name' =>  $response['authorizer_info']['user_name'],
                'alias' =>  $response['authorizer_info']['alias'],
                'qrcode_url'  =>  $response['authorizer_info']['qrcode_url'],
                'principal_name'  => $response['authorizer_info']['principal_name'],
                'authorizer_appid'  => $response['authorization_info']['authorizer_appid'],
                'open_pay' => $response['authorizer_info']['business_info']['open_pay'],
                'open_shake' => $response['authorizer_info']['business_info']['open_shake'],
                'open_scan' => $response['authorizer_info']['business_info']['open_scan'],
                'open_card' => $response['authorizer_info']['business_info']['open_card'],
                'open_store' => $response['authorizer_info']['business_info']['open_store'],
                'func_info' => $func_info
            );
            $this->publicModel->addData($data);
        }
        return $data;
    }

    /**
     * 获取公众号appid
     * @param string 公众号public_id
     * @return string appid
     */
    public function getAuthorizerAppid($user_name)
    {
        if ($authorizer_appid = M('kdgx_plat_public')->where(array('user_name'=>$user_name))->getField('authorizer_appid')) {
            return $authorizer_appid;
        } else {
            return false;
        }
    }
}
