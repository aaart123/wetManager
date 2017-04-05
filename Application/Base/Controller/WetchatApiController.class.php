<?php
namespace Base\Controller;

use Base\Controller\BaseController;
use Base\Controller\OauthApiController;

// 口袋高校第三方开发核心类
class WetchatApiController extends BaseController
{

    public function __construct()
    {
        parent:: __construct();
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    /**
     * 通过public_id获取access_token
     * @param string public_id
     * @return string access_token
     */
    public function getAccessToken()
    {
        $oauthApi = new OauthApiController();
        $appId = $oauthApi->getAuthorizerAppid($this->publicId);
        return $oauthApi->getAuths($appId);
    }

/********************************************  消息管理API   ***********************************************************/
    public function casekfMessage()
    {
        $param = file_get_contents('kf.log');
        $param = json_decode($param, true);
        $auth_code = trim(str_replace("QUERY_AUTH_CODE:","",$param['Content']));
        $token = file_get_contents('case.log');
        $token = json_decode($token, true);
        $data = [
            'touser'=>$param['FromUserName'],
            'msgtype'=>'text',
            'text'=>[
                'content'=>$auth_code.'_from_api'
            ]
        ];
        $data = json_encode($data);
        $authorizer_access_token = $token['authorization_info']['authorizer_access_token'];
        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$authorizer_access_token}";
        $response = json_decode(httpRequest($url, $data), true);
        print_r($response);
    }

    // 发送客服消息
    public function sendKfMessage($authorizer_appid, $data)
    {
        $publicInfo = $this->getPublicInfo($authorizer_appid);
        $func_info = explode(',', $publicInfo['func_info']);
        if (!in_array('1', $func_info)) {
            echo "<script>alert('未获得该公众号的客服接口授权');</script>";
            exit();
        }
        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$authorizer_access_token}";
        $response = json_decode(httpRequest($url, $data), true);
        if ($response['errcode'] == 0) {
            echo "<script>alert('消息发送成功');</script>";
            exit();
        } else {
            echo "<script>alert('消息发送失败');</script>";
            exit();
        }
    }
/********************************************  用户管理API   ***********************************************************/
    /**
     * 获取用户基本信息
     * @param string appid
     * @param string openid
     * @return array 用户信息
     */
    protected function getOpenidInfo($authorizer_appid, $openid)
    {
        $authorizer_access_token = $this->getAuths($authorizer_appid);
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token={$authorizer_access_token}&openid={$openid}&lang=zh_CN";
        $response = httpRequest($url);
        return $response;
    }

    /**
     * 批量获取用户信息列表
     * @param str appid
     * @param arr openid数据
     * @return arr 用户信息
     */
    protected function getOpenidInfoList($authorizer_appid, $data)
    {
        $authorizer_access_token = $this->getAuths($authorizer_appid);
        $url = "https://api.weixin.qq.com/cgi-bin/user/info/batchget?access_token={$authorizer_access_token}";
        $response = httpRequest($url, $data);
        $response = json_decode($response, true);
        return $response;
    }

    /**
     * 批量获取用户列表
     * @param string appid
     * @param string nextOpenid 默认为空
     * @return array 关注者列表
     */
    protected function getOpenidList($authorizer_appid, $nextOpenid = '')
    {
        if (empty($nextOpenid)) {
            $url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token={$authorizer_access_token}";
        } else {
            $url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token={$authorizer_access_token}&next_openid={$nextOpenid}";
        }
        $authorizer_access_token = $this->getAuths($authorizer_appid);
        $response = httpRequest($url);
        $response = json_decode($response, true);
        return $response;
    }

    // 获取粉丝信息
    public function getFansList($publicId)
    {
        $oauthApi = new OauthApiController();
        $appId = $oauthApi->getAuthorizerAppid($this->publicId);
        $userOpenidModel = D('userOpenid');
        while (1) {
            $nextOpenid = $openList['next_openid'];
            $openList = $this->getOpenidList($appid, $nextOpenid);
            $count = 0;
            foreach ($opendList['data']['openid'] as $openid) {
                $temp = ['openid' => $openid];
                $data['user_list'][] = $temp;
                $count ++;
                if ($count > 100) {
                    $openids = $this->getOpenidInfoList($appid, $data);
                    foreach ($openids['user_info_list'] as $value) {
                        $value['public_id'] = $publicId;
                        if (!$userOpenidModel->getOpenidData($value['openid'])) {
                            $userOpenidModel->add($value);
                        }
                    }
                }
            }
            if (empty($openList['next_openid'])) {
                break;
            }
        }
        return true;
    }
/********************************************  自定义菜单API ***********************************************************/
    
    /**
     * 创建菜单
     * @param string 菜单字符串
     */
    public function createMenu($data)
    {
        $token = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token={$token}";
        return httpRequest($url, $data);
    }

    /**
     * 查询菜单
     */
    public function getMenu()
    {
        $token = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/menu/get?access_token={$token}";
        $data = json_decode(httpRequest($url, $data), true);
        return $data;
    }

    /**
     * 删除菜单
     */
    public function deleteMenu()
    {
        $token = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/menu/delete?access_token={$token}";
        $data = json_decode(httpRequest($url, $data), true);
        return $data;
    }

    /**
     * 获取公众号菜单配置
     */
    public function getMenuInfo()
    {
        $token = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/get_current_selfmenu_info?access_token={$token}";
        $data = json_decode(httpRequest($url, $data), true);
        return $data['selfmenu_info'];
    }

/********************************************  素材管理API   ***********************************************************/
    /**
     * 获取素材列表
     * @param string 公众号id
     * @param string 类型 图片image视频video语音voice图文news
     * @param int 偏移位置
     * @param int 数量0-20
     */
    public function getMediaList($type = 'image', $offset = 0, $count = 20)
    {
        $token = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token={$token}";
        $data = json_encode([
            'type' => $type,
            'offset' => $offset,
            'count' => $count
        ]);
        return json_decode(httpRequest($url, $data), true);
    }

    /**
     * 获取素材总数
     * @param string 公众号id
     */
    public function getMediaCount()
    {
        $token = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/material/get_materialcount?access_token={$token}";
        return json_decode(httpRequest($url), true);
    }

    /**
     * 新增永久图文素材
     * @param string 公众号id
     * @param string 图文array数据
     */
    protected function addNews(array $data)
    {
        $data = json_encode($data);
        $token = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/material/add_news?access_token={$token}";
        return json_decode(httpRequest($url), true);
    }

    /**
     * 新增永久其他素材
     * @param string 公众号id
     * @param array 素材array数据
     */
    protected function addMaterial(array $data)
    {
        $token = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/material/add_material?access_token={$token}";
        $header = ['Content-Type: multipart/form-data'];
        return json_decode(httpRequest($url, $data, $header), true);
    }

/* ------------------------------------------  数据统计      ---------------------------------------------------------- */

    /**
     * 获取用户增减数据
     */
    public function getUserSummary()
    {
        $this->publicId = 'gh_c75321282c18';
        $token = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/datacube/getusersummary?access_token={$token}";
        $data = '{
            "begin_date": "2017-03-11", 
            "end_date": "2017-03-11"
        }';
        $response = json_decode(httpRequest($url, $data));
        var_dump($response);
    }

    /**
     * 获取累计用户数据
     */
    public function getUserCumulate()
    {
        $this->publicId = 'gh_c75321282c18';
        $token = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/datacube/getusercumulate?access_token={$token}";
        $data = '{
            "begin_date": "2017-03-11", 
            "end_date": "2017-03-11"
        }';
        $response = json_decode(httpRequest($url, $data));
        var_dump($response);
    }

    /**
     * 获取图文群发每日数据
     */
    public function getArticleSummary()
    {
        $this->publicId = 'gh_c75321282c18';
        $token = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/datacube/getarticlesummary?access_token={$token}";
        $data = '{
            "begin_date": "2017-03-11", 
            "end_date": "2017-03-11"
        }';
        $response = json_decode(httpRequest($url, $data));
        print_r($response);
    }

    /**
     * 获取图文群发总数据
     */
    public function getArticleTotal()
    {
        $token = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/datacube/getarticletotal?access_token={$token}";
        $data = '{
            "begin_date": "",
            "end_date": ""
        }';
        $response = json_decode(httpRequest($url, $data));
        print_r($response);
    }

    /**
     * 获取图文统计数据
     */
    public function getUserRead()
    {
        $token = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/datacube/getuserread?access_token={$token}";
        $data = '{
            "begin_date": "",
            "end_date": ""
        }';
        $response = json_decode(httpRequest($url, $data));
        print_r($response);   
    }

    /**
     * 获取图文统计分时数据
     */
    public function getUserReadHour()
    {
        $token = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/datacube/getuserreadhour?access_token={$token}";
        $data = '{
            "begin_date": "",
            "end_date": ""
        }';
        $response = json_decode(httpRequest($url, $data));
        print_r($response);   
    }

    /**
     * 获取图文分享转发数据
     */
    public function getUserShare()
    {
        $token = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/datacube/getusershare?access_token={$token}";
        $data = '{
            "begin_date": "",
            "end_date": ""
        }';
        $response = json_decode(httpRequest($url, $data));
        print_r($response);   
    }

    /**
     * 获取图文分享转发分时数据
     */
    public function getUserShareHour()
    {
        $token = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/datacube/getusersharehour?access_token={$token}";
        $data = '{
            "begin_date": "",
            "end_date": ""
        }';
        $response = json_decode(httpRequest($url, $data));
        print_r($response);   
    }

    /**
     * 获取消息发送概况数据
     */
    public function getUpStreamMsg()
    {
        $token = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/datacube/getupstreammsg?access_token={$token}";
        $data = '{
            "begin_date": "",
            "end_date": ""
        }';
        $response = json_decode(httpRequest($url, $data));
        print_r($response);   
    }

    /**
     * 获取消息分送分时数据
     */
    public function getUpStreamMsgHour()
    {
        $token = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/datacube/getupstreammsghour?access_token={$token}";
        $data = '{
            "begin_date": "",
            "end_date": ""
        }';
        $response = json_decode(httpRequest($url, $data));
        print_r($response);   
    }

    /**
     * 获取消息发送周数据
     */
    public function getUpStreamMsgWeek()
    {
        $token = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/datacube/getupstreammsgweek?access_token={$token}";
        $data = '{
            "begin_date": "",
            "end_date": ""
        }';
        $response = json_decode(httpRequest($url, $data));
        print_r($response);   
    }

    /**
     * 获取消息发送月数据
     */
    public function getUpStreamMsgMonth()
    {
        $token = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/datacube/getupstreammsgmonth?access_token={$token}";
        $data = '{
            "begin_date": "",
            "end_date": ""
        }';
        $response = json_decode(httpRequest($url, $data));
        print_r($response);   
    }

    /**
     * 获取消息发送分布数据
     */
    public function getUpStreamMsgDist()
    {
        $token = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/datacube/getupstreammsgdist?access_token={$token}";
        $data = '{
            "begin_date": "",
            "end_date": ""
        }';
        $response = json_decode(httpRequest($url, $data));
        print_r($response);   
    }

    /**
     * 获取消息发送分布周数据
     */
    public function getUpStreamMsgDistWeek()
    {
        $token = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/datacube/getupstreammsgdistweek?access_token={$token}";
        $data = '{
            "begin_date": "",
            "end_date": ""
        }';
        $response = json_decode(httpRequest($url, $data));
        print_r($response);   
    }

    /**
     * 获取消息发送分布月数据
     */
    public function getUpStreamMsgDistMonth()
    {
        $token = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/datacube/getupstreammsgdistmonth?access_token={$token}";
        $data = '{
            "begin_date": "",
            "end_date": ""
        }';
        $response = json_decode(httpRequest($url, $data));
        print_r($response);   
    }

    /**
     * 获取接口分析数据
     */
    public function getInterfaceSummary()
    {
        $token = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/datacube/getinterfacesummary?access_token={$token}";
        $data = '{
            "begin_date": "",
            "end_date": ""
        }';
        $response = json_decode(httpRequest($url, $data));
        print_r($response);   
    }

    /**
     * 获取接口分析数据
     */
    public function getInterfaceSummaryHour()
    {
        $token = $this->getAccessToken();
        $url = "https://api.weixin.qq.com/datacube/getinterfacesummaryhour?access_token={$token}";
        $data = '{
            "begin_date": "",
            "end_date": ""
        }';
        $response = json_decode(httpRequest($url, $data));
        print_r($response);   
    }

}
