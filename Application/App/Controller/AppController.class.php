<?php
namespace App\Controller;

use Base\Controller\CommonController;

/**
 * 应用处理类
 */
class AppController extends CommonController
{

    public function __construct()
    {
        parent:: __construct();
        $this->appModel = D('Base/app');
        $this->publicKeyModel = D('Base/PublicKey');
    }

    /**
     * 获取应用信息
     * @param int 应用id
     * @return arr 应用信息
     */
    public function getAppData($appId)
    {
        return $this->appModel->getData($appId);
    }

    /**
     * 是否开启某个应用
     * @param str 公众号id
     * @param int 应用id
     * @return boolean 
     */
    public function isOpen($publicId, $appId)
    {
        if ($this->publicKeyModel->getKeysApp($publicId, $appId, 'app')) {
            return true;
        } else {
            return false;
        }
    }
 
    /**
     * 关闭应用
     * @param str 公众号id
     * @param int 应用id
     */
    public function closeApp($publicId, $appId)
    {
        $where = [
            'public_id' => $publicId,
            'strategy_id' => $appId,
            'type' => 'app'
        ];
        return $this->publicKeyModel->deleteKey($where);
    }
    
    /**
     * 是否存在关键字冲突
     * @param int 公众号id
     * @param string 关键字
     */
    public function isHasKeyword($publicId, $keywords)
    {
        foreach ($keywords as $keyword) {
            if ($keys = $this->publicKeyModel->getKeyStrategy($publicId, $keyword['keyword'])) {
                return false;
            }
        }
        return true;
    }

    /**
     * 添加应用配置
     * @param str 公众号id
     * @param arr 配置数组
     * @param int 应用id
     */
    public function addAppConfig($publicId, $configs, $appId)
    {
        foreach ($configs as $config) {
            $data['public_id'] = $publicId;
            $data['type'] = 'app';
            $data['strategy_id'] = $appId;
            $data['keyword'] = $config;
            !$this->publicKeyModel->addData($data) && E('配置更新失败');
        }
        return true;
    }

    /**
     * 更新应用配置
     * @param arr 配置数组
     */
    public function updateAppConfig($publicId, $configs, $appId)
    {
        foreach ($configs as $config) {
            $where['public_id'] = $publicId;
            $where['type'] = 'app';
            $where['strategy_id'] = $appId;
            !$this->publicKeyModel->updateData($where, $config) && E('配置更新失败');
        }
        return true;
    }

    /**
     * 获取所有应用列表
     */
    public function getAppList()
    {
        $appList = $this->appModel->getAppList();
        return $appList;
    }

    /**
     * 获取开启的应用列表
     * @param str 公众号id
     */
    public function getAppListByPublic($publicId)
    {
        if ($keys = $this->publicKeyModel->getKeys($publicId, 'app')){
            $apps = array();
            foreach ($keys as $key) {
                array_push($apps, $key['strategyId']);
            }
            empty($apps) && $where['app_id'] = ['in', $apps];
            $appList = $this->appModel->getAppList($where);
            return $appList;
        } else {
            return array();
        }
    }
}
