<?php
namespace Base\Model;

use Base\Model\BaseModel;

class AppModel extends BaseModel
{
    protected $trueTableName = 'kdgx_plat_app';

    protected $_map = [
        'appId' => 'app_id',
        'openUrl' => 'open_url'
    ];

    public function __construct()
    {
        parent:: __construct();
    }

    /**
     * 获得app信息
     *@param int appId
     *@return array app信息
     */
    public function getData($appId)
    {
        $where = array(
            'app_id' => $appId
        );
        $app = $this->where($where)->find();
        $app = $this->parseFieldsMap($app);
        return $app;
    }

    /**
     * 获取应用信息列表
     * @param arr 添加数组
     * @param arr 应用列表
     */
    public function getAppList($where = array())
    {
        if (empty($where)) {
            $apps = $this->select();
        } else {
            $apps = $this->where($where)->select();
        }
        $apps = $this->parseFieldsMap($apps);
        return $apps;
    }

    /**
     * 获取应用secret
     * @param string 应用key
     * @return array 应用信息
     */
    public function getDataSecret($apiKey)
    {
        $where = [
            'api_key' => $apiKey
        ];
        $data = $this->where($where)->select();
        return $this->parseFieldsMap($data);
    }
}
