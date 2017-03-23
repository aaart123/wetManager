<?php
namespace Event\Controller;

use Base\Controller\BaseController;
use Base\Controller\WetchatApiController;

/**
 * 菜单事件处理
 */
class MenuController extends BaseController
{
    private $wetApi;

    public function __construct()
    {
        parent::__construct();
        $this->wetApi = new WetchatApiController();
    }

    /**
     * 获取菜单事件
     */
    public function getMenuEvent()
    {
        $menus = $this->wetApi->getMenuInfo();
        foreach ($menus['button'] as &$menu) {
            $data = $this->eventModel->getEventStrategy($this->publicId, $menu['type'], $menu['name']);
            $data = $data['0'];
            $menu['msgType'] = $data['type'];
            $menu['strategyId'] = $data['strategyId'];
        }
        return $menus;
    }

    /**
     * 添加菜单事件
     * @param array 数据
     */
    public function createMenuEvent($data)
    {
        if ($this->eventModel->getEventStrategy($this->publicId, $data['event'], $data['key'])) {
            $where = [
                'public_id' => $this->publicId,
                'event_key' => $data['key']
            ];
            unset($data['key']);
            if ($this->eventModel->updateData($where, $data)) {
                return true;
            }
        } else {
            if ($this->eventModel->addData($data)) {
                return true;
            }
        }
        return false;
    }

}
