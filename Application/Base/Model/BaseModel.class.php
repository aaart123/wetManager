<?php
namespace Base\Model;

use Think\Model;

/**
 * 基础类
 */
class BaseModel extends Model
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

    protected $publicId;

    protected $_map = [
        'createTime' => 'create_time',
        'modifiedTime' => 'modified_time'
    ];
    
    protected $_auto = array(
        array('create_time', 'time', self::MODEL_INSERT, 'function'),
        array('modified_time', 'time', self::MODEL_UPDATE, 'function')
    );

    public function __construct()
    {
        parent:: __construct();
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    /**
     * 处理字段映射重载
     * @param array $data 当前数据
     * @param integer $type 类型 0 写入 1 读取
     * return array
     */
    public function parseFieldsMap($data, $type = 1)
    {
        // 检查字段映射
        if (!empty($this->_map)) {
            foreach ($data as $dkey => $dval) {
                if (is_array($dval)) {
                    $data[$dkey] = self::parseFieldsMap($dval, $type);
                } else {
                    foreach ($this->_map as $key => $val) {
                        if ($type==1) { # 读取
                            if (isset($data[$val])) {
                                $data[$key] = $data[$val];
                                unset($data[$val]);
                            }
                        } else {
                            if (isset($data[$key])) {
                                $data[$val] = $data[$key];
                                unset($data[$key]);
                            }
                        }
                    }
                    return $data;
                }
            }
        }
        return $data;
    }
}
