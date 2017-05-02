<?php

namespace Console\Controller;

use Think\Controller;
use Console\Model\WenzhangModel;
use Wap\Model\UserModel;
use Wap\Model\ArticleModel;
use Wap\Model\PublicSubscribeModel;
use Wap\Controller\BlackController;

class NewrankController extends Controller
{
    private $WenzhangModel;
    private $articleModel;
    private $blackActivity;

    public function __construct()
    {
        parent::__construct();
        $this->WenzhangModel = new WenzhangModel();
        $this->articleModel = new ArticleModel();
        $this->blackActivity = new BlackController();
    }

    /**
     * 发送curl请求
     */
    private function https_post_array($url, $data = null, $header)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 120);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLINFO_HEADER_OUT, true);
        if (!empty($data)) {
            $param = '';
            foreach ($data as $k => $v) {
                $param.= urlencode($k).'='.urlencode($v).'&';
            }
            $param = rtrim($param, '&');
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $param);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        // print_r(curl_getinfo($curl));
        $output = curl_exec($curl);
        if (false === $output) {
            echo curl_error($curl);
            return false;
        }
        curl_close($curl);
        return $output;
    }

    /**
     * 获取需要抓取得公众号
     */
    private function getAlias()
    {
        $public = D('Wap/Public')->field('user_name,alias')->select();
        foreach ($public as &$value) {
            empty($value['alias']) && $value['alias'] = $value['user_name'];
        }
        return $public;
    }

    /**
     * 获取uuid
     * @param string 公众号别名
     */
    private function getuuid($account)
    {
        $url = "http://www.newrank.cn/public/info/detail.html?account={$account}";
        $html=file_get_contents($url);
        preg_match("/var fgkcdg =([{\s\S]+})/",$html,$data);
        $data=json_decode($data['1'], true);
        return $data;
    }

    /**
     * 获取文章列表
     * @param int uuid
     */
    private function getAriticles($uuid)
    {
        $url = "http://www.newrank.cn/xdnphb/detail/getAccountArticle";
        $header = array(
            'X-Requested-With: XMLHttpRequest',
            'Host: www.newrank.cn',
            'Referer: http://www.newrank.cn/public/info/detail.html',
            'Content-Type: application/x-www-form-urlencoded;charset=utf-8', 
            'Origin: http://www.newrank.cn'
        );
        $data = [
            'flag' => 'true',
            'uuid' => $uuid
        ];
        $response = $this->https_post_array($url, $data, $header);
        return $response;
    }

    /**
     * 维护微信文章
     */
    public function insertWenzhang(&$article)
    {
        $where['unique_id'] = $article['id'];
        $data['message_id'] = $article['messageId'];
        $data['author'] = $article['author'];
        $data['clicks_count'] = $article['clicksCount'];
        $data['like_count'] = $article['likeCount'];
        $data['title'] = $article['title'];
        $data['highlight'] = $article['highlight'];
        $data['summary'] = $article['summary'];
        $data['url'] = $article['url'];
        $data['video_url'] = $article['videoUrl'];
        $data['order_num'] = $article['orderNum'];
        $data['ori_author'] = $article['oriAuthor'];
        $data['public_time'] = strtotime($article['publicTime']);
        if ($result = $this->WenzhangModel->getAll($where)) {
            $data['id'] = $result[0]['id'];
            $this->WenzhangModel->editData($where, $data);
        } else {
            $data['unique_id'] = $article['id'];
            print_r($data);
            $this->WenzhangModel->addData($data);
        }
        $data['unique_id'] = $article['id'];
        return $data;
    }

    /**
     * 维护圈子动态
     */
    public function insertArticle($article)
    {
        $where['unique_id'] = $article['unique_id'];
        $data['user_id'] = $article['user_id'];
        $data['content'] = $article['title'];
        $data['clicks_count'] = $article['clicks_count'];
        $data['like_count'] = $article['like_count'];
        $data['publicname'] = $article['user_id'];
        $data['url'] = $article['url'];
        $data['public_time'] = $article['public_time'];
        $black = ['gh_db45517db611','wxid_1649226491711'];
        if (!in_array($article['user_id'], $black) 
            && !$this->blackActivity->isBlack($article['user_id']) 
            && $article['public_time'] > strtotime('-3 day') 
            && ($data['clicks_count'] > 1200 && $data['like_count'] > 20)) {
            $data['is_delete'] = 0;
            if ($result = $this->articleModel->getAll($where)) {
                $data['article_id'] = $result[0]['article_id'];
                $this->articleModel->editData($where, $data);
                return false;
            } else {
                $data['unique_id'] = $article['unique_id'];
                print_r($data);
                $article_id = $this->articleModel->addData($data);
                $this->sendWenzhangNotive($article, $article_id);
                return true;
            }
        } else {
            $data['is_delete'] = 2;
            if ($result = $this->articleModel->getAll($where)) {
                $data['article_id'] = $result[0]['article_id'];
                $this->articleModel->editData($where, $data);
                return false;
            } else {
                $data['unique_id'] = $article['unique_id'];
                print_r($data);
                $article_id = $this->articleModel->addData($data);
                return false;
            }
        }
    }

    /**
     * 获取公众号的被关注列表
     */
    public function getOpenids($public_id)
    {
        $publicSubscribe = new PublicSubscribeModel();
        $psWh['public_id'] = $public_id;
        $psWh['state'] = 1;
        $users = $publicSubscribe->getAll($psWh);
        $userModel = new UserModel();
        $openids = [];
        foreach ($users as $user) {
           $openids[] = $userModel->field('user_id, openid')->where(['user_id'=>$user['user_id']])->find();
        }
        return $openids;
    }

    private function sendWenzhangNotive($article, $article_id)
    {
        $openids = $this->getOpenids($article['user_id']);
        $publicSubscribe = new PublicSubscribeModel();
        foreach ($openids as $openid) {
            $wh['user_id'] = $openid['user_id'];
            $wh['is_subscribe'] = 1;
            if (!D('Wap\Conf')->where($wh)->find()) {
                continue;
            }
            $array=[
                'openid'=> $openid['openid'],
                'url'=>"http://www.koudaidaxue.com/index.php/wap/index/index?page=detail?id={$article_id}",
                'first'=>$article['author'].'有更新
                         ',
                'keyword1'=> mb_substr($article['title'], 0, 10).'...',
                'keyword2'=> $article['publicTime'] .'更新',
                'remark'=>'
点此查看详情'
            ];
            $obj = new \Base\Controller\WetchatApiController();
            $obj->publicId = 'gh_243fe4c4141f';
            $response = $obj->setSubscribeTemplate($array);
            $where['public_id'] = $article['user_id'];
            $where['user_id'] = $openid['user_id'];
            $save['send_time'] = time();
            $publicSubscribe->editData($where, $save);
        }
    }

    /**
     * 启动爬虫
     */
    public function spider()
    {
        $accounts = $this->getAlias();
        foreach ($accounts as $value) {
            $public = $this->getuuid($value['alias']);
            $uuid = $public['uuid'];
            $info = json_decode($this->getAriticles($uuid), true);
            $articles = $info['value']['lastestArticle'];
            foreach ($articles as &$article) {
                $data = $this->insertWenzhang($article);
                if (!($data['clicks_count'] > 1200 && $data['like_count'] > 20)) {
                    $data['user_id'] = D('Wap/Public')->where(['nick_name'=> $data['author']])->getField('user_name');
                    if (empty($data['user_id'])) {
                        continue ;
                    }
                    $this->insertArticle($data);
                }
            }
        }
    }

    /**
     * 发布动态脚本
     */
    public function auto()
    {   
        $map['clicks_count'] = ['gt', 1200];
        $map['like_count'] = ['gt', 20];
        $map['_logic'] = 'and';
        $where['_complex'] = $map;
        $where['create_time'] = ['between', [strtotime('-1 day'), time()]];
        $articles = $this->WenzhangModel->getAll($where);
        foreach ($articles as $article) {
            $article['user_id'] = D('Wap/Public')->where(['nick_name'=> $article['author']])->getField('user_name');
            if ($this->insertArticle($article)) {
                return; 
            } else {
                echo 'false';
            }
        }
    }
}