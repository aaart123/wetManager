<?php
namespace Base\Model;

use Base\Model\BaseModel;

/**
 * public_key 数据模型
 */

class PublicKeyModel extends BaseModel
{
    // 实际数据表名
    protected $trueTableName = 'kdgx_plat_public_key';

    protected $_map = [
        'keyId' => 'key_id',
        'publicId' => 'public_id',
        'isEqual' => 'is_equal',
        'strategyId' => 'strategy_id'
    ];

    public function __construct()
    {
        parent:: __construct();
    }

    /**
     * 添加公众号关键字
     * @param array 数据数组
     * @return int 结果
     */
    public function addData($data)
    {
        !$this->create($data) && $this->getError();
        return $this->add($data);
    }

    /**
     * 获取公众号的关键字
     * @param str 公众号id
     * @param int 关键字类型
     * @return arr 关键字数组
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
     * 获取策略的关键字
     * @param str 公众号id
     * @param int 策略id
     * @param str 策略类型
     * @return arr 关键字数组
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
     * 获取公众号关键字策略
     * @param str 公众号id
     * @param str 关键字
     * @return arr 关键字数组
     */
    public function getKeyStrategy($publicId, $keyword, $type = '')
    {
        $where = [
            'public_id' => $publicId,
            'keyword' => $keyword
        ];
        if (!empty($type)) {
            $where['type'] = $type;
        }
        $key = $this->where($where)->select();
        return $this->parseFieldsMap($key);
    }

    /**
     * 删除公众号关键字
     * @param arr 删除条件
     */
    public function deleteKey($where)
    {
        return $this->where($where)->delete();
    }

    /**
     * 更新公众号关键字
     * @param arr 条件数组
     * @param str 关键字
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
