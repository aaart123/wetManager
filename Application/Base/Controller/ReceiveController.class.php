<?php
namespace Base\Controller;

use \Base\Controller\OauthApiController;

// 第三方服务事件接收类
class ReceiveController extends OauthApiController
{
    private $component_appid;   // 第三方服务的appid
    private $msgActivity;
    protected $wxBizMsgCrypt; // 加密解密类


    public function __construct()
    {
        parent::__construct();
        $this->component_appid = C('COMPONENT_APPID');
        $this->msgActivity = A('Msg');
        $this->wxBizMsgCrypt = new \Common\Common\wxBizMsgCrypt(C('TOKEN'), C('ENCODINGAESKEY'), C('COMPONENT_APPID'));
    }

    // 授权事件接收URL
    public function Auth()
    {
        $msg = ''; // 第三方收到公众平台发送的消息
        $errCode = $this->decryptMsg($msg);
        if ($errCode == 0) {
            $param = xml2Arr($msg);
            switch ($param['InfoType']) {
                case 'component_verify_ticket':     // 授权凭证
                    $component_verify_ticket = $param['ComponentVerifyTicket'];
                    $ret['component_verify_ticket'] = $component_verify_ticket;
                    file_put_contents('component_verify_ticket', $component_verify_ticket);     // 缓存
                    break;
                case 'unauthorized': // 取消授权
                    $state = 0;
                    M('kdgx_plat_authorizer')->where(array('authorizer_appid'=>$param['AuthorizerAppid']))->save(array('authorization_state'=>$state));
                    break;
                case 'authorized': // 授权成功
                    $sql = "INSERT INTO kdgx_plat_authorizer (authorizer_appid,authorization_code,authorizer_code_expires_in,authorization_state) VALUES('"
                    .$param['AuthorizerAppid']."','"
                    .$param['AuthorizationCode']."',"
                    .$param['AuthorizationCodeExpiredTime'].",'1') ON DUPLICATE KEY UPDATE authorization_state='1'";
                    M()->execute($sql);
                    break;
                case 'uodateauthorized': // 更新授权
                    if (M('kdgx_plat_authorizer')->where(array('authorizer_appid'=>$param['AuthorizerAppid']))->find()) {
                        $save = array(
                        'authorization_code'=>$param['AuthorizationCode'],
                        'authorizer_code_expires_in'=>$param['AuthorizationCodeExpiredTime']
                            );
                        M('kdgx_plat_authorizer')->where(array('authorizer_appid'=>$param['AuthorizerAppid']))->save($save);
                    } else {
                        $sql = "INSERT INTO kdgx_plat_authorizer (authorizer_appid,authorization_code,authorizer_code_expires_in,authorization_state) VALUES('"
                        .$param['AuthorizerAppid']."','"
                        .$param['AuthorizationCode']."',"
                        .$param['AuthorizationCodeExpiredTime'].",'1') ON DUPLICATE KEY UPDATE authorization_state='1'";
                        M()->execute($sql);
                    }
                    break;
            }
            echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
            exit();
        } else {
            echo '<xml><return_code><![CDATA[FAILED]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
            exit();
        }
    }

    /**
     * 接收公众号消息与事件数据包入口
     */
    public function Eventmessage()
    {
        $errCode = $this->decryptMsg($msg);
        // fil
        // $msg = '<xml><ToUserName><![CDATA[gh_19fb1bed539e]]></ToUserName>
        //     <FromUserName><![CDATA[oIFqdt9TSj1p-ZB1n2NMeyrkD88o]]></FromUserName>
        //     <CreateTime>1490256171</CreateTime>
        //     <MsgType><![CDATA[text]]></MsgType>
        //     <Content><![CDATA[关键字]]></Content>
        //     <MsgId>6400601517522745735</MsgId>
        //     </xml>';
        $errCode = 0;
        if ($errCode == 0) {
            $param = xml2Arr($msg);
            $this->msgActivity->replyMsg($param);
        } else {
            echo '解密失败';
        }
    }

    // 接收消息体解密
    private function decryptMsg(&$msg)
    {
        $timeStamp    = trim($_GET['timestamp']);
        $nonce        = trim($_GET['nonce']);
        $msg_sign     = trim($_GET['msg_signature']);
        $encryptMsg     = file_get_contents('php://input');
        // $timeStamp = 1483765004;
            // 	$nonce = 1224264125;
            // 	$msg_sign = '35a93cea1490bd0fef82245276e69c21aa3baa01';
            // 	$encryptMsg = '<xml>
            //  <ToUserName><![CDATA[gh_19fb1bed539e]]></ToUserName>
            //  <Encrypt><![CDATA[PR4qEQwc/amc1zmaAxkVL7DIIyMojqsUVwiiPvmHSJOtjLEa9mQbqe88YLAsxwho8uZTkwAAvIPL315badnK4yEw7h3fVjiVHPUtGBWzMm8HmIQszesn5xp1tgTRg6BRIY9k2z7FuB1HWARB6WI3YWyShDA34oNFbSysJ0cKXNrTvyaNkg4TfhEOgfAeqqdsJMbkvYLnCacY31AFfxXId+f4kHtpO8CyfOFSXMeCSVEp3dX0essaBNaXQXpLsye7ghamjWTBUpCbPNfbH8jTHBm08OoewphQFDSsn2UIdk7P8eSnsdHncj5oO6NGNcV0k+lQdRPMlILFIdVDT1Ezvtc0nUr/CzPJYn8EOFCx0e3JCEo9FsLEZloACodbzUricreXIg/l8KWd7mUNRNU9dAvt9/MvJeO1URrCPitrTTA=]]></Encrypt>
            //  </xml>';
            // file_put_contents('msg.log', 'timeStamp:'.$timeStamp.' nonce:'.$nonce.' msg_sign:'.$msg_sign.' encryptMsg:'.$encryptMsg);
        $postArr = xml2Arr($encryptMsg);    // xml对象解析为数组
        $format = "<xml><ToUserName><![CDATA[toUser]]></ToUserName><Encrypt><![CDATA[%s]]></Encrypt></xml>";
        $from_xml = sprintf($format, $postArr['Encrypt']);
        $errCode = $this->wxBizMsgCrypt->decryptMsg($msg_sign, $timeStamp, $nonce, $from_xml, $msg);    // 解密
        return $errCode;
    }

    // 进入授权页
    public function authorizer_access_token()
    {
        $pre_auth_code = $this->getPre_auth_code();
        $redirect_uri = 'http://www.koudaidaxue.com/index.php/base/Receive/Redirect';
        $redirect_uri = urlencode($redirect_uri);
        echo "<script>location.href='https://mp.weixin.qq.com/cgi-bin/componentloginpage?component_appid={$this->component_appid}&pre_auth_code={$pre_auth_code}&redirect_uri={$redirect_uri}'</script>";
    }

    // 授权成功回调地址
    public function Redirect()
    {
        $auth_code = $_GET['auth_code'];
        $response = $this->getCodeAuths($auth_code);
        // $response['authorization_info']['authorizer_appid'] = 'wx5e843e96fc152d10';
        $publicInfo = $this->getPublicInfo($response['authorization_info']['authorizer_appid']);
        $plat_user_id = session('plat_user_id');
        if (!empty($plat_user_id)) {
            A('User/PublicUser')->addPublicList($publicInfo['user_name'], $plat_user_id);
            D('User/PublicUser')->setPublicAdminMain($publicInfo['user_name'], $plat_user_id);
        }
        $this->urlRedirect(U('Home/Index/Index'));
    }

    // 使用授权码换取公众号的接口调用凭据和授权信息
    private function getCodeAuths($auth_code)
    {
        $data = M('kdgx_plat_authorizer')->where(array('authorization_code'=>$auth_code))->find();
        if (!$data || $data['authorizer_code_expires_in'] < time() || empty($data['authorizer_refresh_token'])) {
            $component_access_token = $this->getComponent_access_aoken();
            $url = "https://api.weixin.qq.com/cgi-bin/component/api_query_auth?component_access_token={$component_access_token}";
            $data = '{
                "component_appid":"'.$this->component_appid.'",
                "authorization_code":"'.$auth_code.'"
            }';
            $response = json_decode(httpRequest($url, $data), true);
            if (isset($response['authorization_info'])) {
                $save = array(
                    'authorizer_access_token'=>$response['authorization_info']['authorizer_access_token'],
                    'authorizer_refresh_token'=>$response['authorization_info']['authorizer_refresh_token'],
                    'authorizer_access_token_expires_in'=>time()+$response['authorization_info']['expires_in']
                    );
                M('kdgx_plat_authorizer')->where(array('authorizer_appid'=>$response['authorization_info']['authorizer_appid']))->save($save);
                return $response;
            } else {
                return false;
            }
        } else {
            $response['authorization_info'] = $data;
            return $response;
        }
    }
}
