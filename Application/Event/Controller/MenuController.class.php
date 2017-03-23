<?php
namespace Event\Controller;

use Event\Controller\BaseController;
use Base\Controller\WetchatApiController;

use Base\Model\EventModel;

/**
 * 菜单事件处理
 */
class MenuController extends BaseController
{

    private $wetApi;
    private $eventModel;

    public function __construct($publicId)
    {
        parent::__construct();
        $this->publicId = $publicId;
        $this->wetApi = new WetchatApiController();
        $this->wetApi->publicId = $publicId;
        $eventModel = new EventModel();
        $eventModel->publicId = $this->publicId;
    }

    /**
     * 获取菜单事件
     */
    public function getMenuEvent()
    {
        $menus = $this->wetApi->getMenuInfo();
        foreach ($menus['button'] as &$menu) {
            $data = $this->eventModel->getEventStrategy($this->public, $item['type'], $item['key']);
            $item['type'] = $data['type'];
            $item['strategyId'] = $data['strategyId'];
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
