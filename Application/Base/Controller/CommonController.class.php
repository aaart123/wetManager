<?php
namespace  Base\Controller;

use Base\Controller\BaseController;

/**
 * 业务核心类
 */

class CommonController extends BaseController
{
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

    protected $publicKeyModel;
    protected $eventModel;
    protected $appModel;
    protected $textModel;
    protected $openidModel;
    protected $wxBizMsgCrypt; // 加密解密类

    public function __construct()
    {
        parent::__construct();
        $this->publicKeyModel = D('Base/PublicKey');
        $this->appModel = D('Base/App');
        $this->textModel = D('Base/text');
        $this->openidModel = D('Base/UserOpenid');
        // $this->eventModel = D('Base/Event');
        $this->wxBizMsgCrypt = new \Common\Common\wxBizMsgCrypt(C('TOKEN'), C('ENCODINGAESKEY'), C('COMPONENT_APPID'));
    }
}