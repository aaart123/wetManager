<?php
namespace Event\Controller;

<<<<<<< HEAD
use Base\Controller\BaseController;
use Base\Controller\WetchatApiController;

=======
use Event\Controller\BaseController;
use Base\Controller\WetchatApiController;

use Base\Model\EventModel;

>>>>>>> ceec921bc1e6bc88ee6cd6f194b24a2cdbe918de
/**
 * 菜单事件处理
 */
class MenuController extends BaseController
{
<<<<<<< HEAD
    private $wetApi;

    public function __construct()
    {
        parent::__construct();
        $this->wetApi = new WetchatApiController();
=======

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
>>>>>>> ceec921bc1e6bc88ee6cd6f194b24a2cdbe918de
    }

    /**
     * 获取菜单事件
     */
    public function getMenuEvent()
    {
        $menus = $this->wetApi->getMenuInfo();
        foreach ($menus['button'] as &$menu) {
<<<<<<< HEAD
            $data = $this->eventModel->getEventStrategy($this->publicId, $menu['type'], $menu['name']);
            $data = $data['0'];
            $menu['msgType'] = $data['type'];
            $menu['strategyId'] = $data['strategyId'];
=======
            $data = $this->eventModel->getEventStrategy($this->public, $item['type'], $item['key']);
            $item['type'] = $data['type'];
            $item['strategyId'] = $data['strategyId'];
>>>>>>> ceec921bc1e6bc88ee6cd6f194b24a2cdbe918de
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
