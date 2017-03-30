<?php
namespace Base\Controller;

use Think\Controller;

/**
 * 业务层基础类
 */
class BaseController extends Controller
{
    protected $publicId;

    protected $appModel;
    protected $eventModel;
    protected $mediaModel;
    protected $newsModel;
    protected $publicKeyModel;
    protected $textModel;

    // 消息类型模板
    protected $msgTemplate = array(
        // 文本模板
        'text'=>  '<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[text]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
                    <FuncFlag>1</FuncFlag>
                    </xml>',
        // 图片模板
        'image'=> '<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[%s]]></MsgType>
                    <PicUrl><![CDATA[%s]]></PicUrl>
                    <MediaId><![CDATA[$s]]></MediaId>
                    <MsgId>%s</MsgId>
                    </xml>',
        // 语音消息
        'voice'=> '<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[%s]]></MsgType>
                    <MediaId><![CDATA[%s]]></MediaId>
                    <Format><![CDATA[%s]]></Format>
                    <MsgId>%s</MsgId>
                    </xml>',
        // 视频消息
        'video'=> '<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[%s]]></MsgType>
                    <MediaId><![CDATA[%s]]></MediaId>
                    <ThumbMediaId><![CDATA[%s]]></ThumbMediaId>
                    <MsgId>%s</MsgId>
                    </xml>',
        // 音乐消息
        'music'=> '<xml>+
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[%s]]></MsgType>
                    <Music>
                    <Title><![CDATA[%s]]></Title>
                    <Description><![CDATA[%s]]></Description>
                    <MusicUrl><![CDATA[%s]]></MusicUrl>
                    <HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
                    <ThumbMediaId><![CDATA[%s]]></ThumbMediaId>
                    </Music>
                    </xml>',
        // 图文模板
        'news'=> '<xml>
                <ToUserName><![CDATA[%s]]></ToUserName>
                <FromUserName><![CDATA[%s]]></FromUserName>
                <CreateTime>%s</CreateTime>
                <MsgType><![CDATA[%s]]></MsgType>
                <ArticleCount>%s</ArticleCount>
                <Articles>
                <item>
                <Title><![CDATA[%s]]></Title>
                <Description><![CDATA[%s]]></Description>
                <PicUrl><![CDATA[%s]]></PicUrl>
                <Url><![CDATA[%s]]></Url>
                </item>
                </Articles>
                <FuncFlag>%s</FuncFlag>
                </xml>'
    );

    public function __construct()
    {
        parent::__construct();
        $this->publicId = session('plat_public_id');
        $this->appModel = D('Base/app');
        $this->eventModel = D('Base/event');
        $this->mediaModel = D('Base/media');
        $this->newsModel = D('Base/news');
        $this->publicKeyModel = D('Base/publicKey');
        $this->textModel = D('Base/text');
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    /**
     * URL重定向
     * @param string $url 重定向的URL地址
     * @param integer $time 重定向的等待时间（秒）
     * @param string $msg 重定向前的提示信息
     * @return void
     */
    protected function urlRedirect($url, $time = 0, $msg = '')
    {
        //多行URL地址支持
        $url        = str_replace(array("\n", "\r"), '', $url);
        if (empty($msg)) {
            $msg    = "系统将在{$time}秒之后自动跳转到{$url}！";
        }
        if (!headers_sent()) {
            // redirect
            if (0 === $time) {
                header('Location: ' . $url);
            } else {
                header("refresh:{$time};url={$url}");
                echo($msg);
            }
            exit();
        } else {
            $str    = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
            if ($time != 0) {
                $str .= $msg;
            }
            exit($str);
        }
    }

    /**
     * 过滤字段
     * @param int 数组地址
     * @param array
     */
    protected function unsetField(&$param, $fields)
    {
        foreach ($fields as $field) {
            unset($param[$field]);
        }
    }
}
