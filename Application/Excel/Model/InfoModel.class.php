<?php
namespace Excel\Model;
use Think\Model;

class InfoModel extends Model
{
    protected $tableName = 'kdgx_public_info';
    protected $_auto = array(
        ['province', '', 3, 'ignore'],
        ['city', '', 3, 'ignore'],
        ['school', '', 3, 'ignore'],
        ['area', '', 3, 'ignore'],
        ['type', '', 3, 'ignore'],
        ['number', '', 3, 'ignore'],
        ['fans', '', 3, 'ignore'],
        ['price_one', '', 3, 'ignore'],
        ['price_two', '', 3, 'ignore'],
        ['owner', '', 3, 'ignore'],
        ['level', '', 3, 'ignore']
        );
}