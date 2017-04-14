<?php

namespace Console\Controller;

use Think\Controller;
use Console\Model\WenzhangModel;

class NewrankController extends Controller
{
    private $WenzhangModel;

    public function __construct()
    {
        parent::__construct();
        $this->WenzhangModel = new WenzhangModel();
    }

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

    private function getAlias()
    {
        $public = D('Wap/Public')->field('user_name,alias')->select();
        foreach ($public as &$value) {
            empty($value['alias']) && $value['alias'] = $value['user_name'];
        }
        return $public;
    }

    private function getuuid($account)
    {
        $url = "http://www.newrank.cn/public/info/detail.html?account={$account}";
        $html=file_get_contents($url);
        preg_match("/var fgkcdg =([{\s\S]+})/",$html,$data);
        $data=json_decode($data['1'], true);
        return $data;
    }

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

    public function spider()
    {
        $accounts = $this->getAlias();
        foreach ($accounts as $value) {
            $public = $this->getuuid($value['alias']);
            $uuid = $public['uuid'];
            $info = json_decode($this->getAriticles($uuid), true);
            $articles = $info['value']['lastestArticle'];
            foreach ($articles as &$article) {
                $data['create_time'] = strtotime($article['publicTime']);
                $data['unique_id'] = $article['id'];
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
                $this->WenzhangModel->addData($data);
            }
        }
    }
}