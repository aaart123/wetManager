<?php
namespace Base\Model;

use Base\Model\BaseModel;

class EventModel extends BaseModel
{

    protected $trueTableName;

    protected $_map = [
        'eventId' => 'event_id',
        'publicId' => 'public_id',
        'eventKey' => 'event_key',
        'strategyId' => 'strategy_id'
    ];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 添加公众号事件
     * @param array 数据数组
     * @return int 结果
     */
    public function addData($data)
    {
        !$this->create($data) && $this->getError();
        return $this->add($data);
    }

    /**
     * 获取公众号的事件
     * @param str 公众号id
     * @param int 事件类型
     * @return arr 事件数组
     */
    public function getKeys($publicId, $type = '')
    {
        $where = [
            'public_id' => $publicId
        ];
        if (!empty($type)) {
            $where['type'] = $type;
        }
        $keys = $this->where($where)->select();
        return $this->parseFieldsMap($keys);
    }
    
    /**
     * 获取策略的事件
     * @param str 公众号id
     * @param int 策略id
     * @param str 策略类型
     * @return arr 事件数组
     */
    public function getKeysApp($publicId, $strategyId, $type = '')
    {
        $where = [
            'public_id' => $publicId,
            'strategy_id' => $strategyId
        ];
        if (!empty($type)) {
            $where['type'] = $type;
        }
        $keys = $this->where($where)->select();
        return $this->parseFieldsMap($keys);
    }

    /**
     * 获取公众号事件策略
     * @param str 公众号id
     * @param str 事件
     * @return arr 事件数组
     */
    public function getEventStrategy($publicId, $event, $type = '')
    {
        $where = [
            'public_id' => $publicId,
            'event' => $event
        ];
        if (!empty($type)) {
            $where['type'] = $type;
        }
        $key = $this->where($where)->select();
        return $this->parseFieldsMap($key);
    }

    /**
     * 删除公众号事件
     * @param arr 删除条件
     */
    public function deleteKey($where)
    {
        return $this->where($where)->delete();
    }

    /**
     * 更新公众号事件
     * @param arr 条件数组
     * @param str 事件
     */
    public function updateData($where, $keyword)
    {
        $data = [
            'keyword' => $keyword
        ];
        !$this->create($data) && E('数据更新失败');
        return $this->where($where)->save();
    }
}
