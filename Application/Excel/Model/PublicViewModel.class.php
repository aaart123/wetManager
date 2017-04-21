<?php

namespace Excel\Model;
use Think\Model;

class PublicViewModel extends Model
{
    protected $tableName = 'kdgx_public';
    protected $_auto = array(
        ['public_id', '', 3, 'ignore'],
        ['public_name', '', 3, 'ignore'],
        ['alias_id', '', 3, 'ignore'],
        ['description', '', 3, 'ignore'],
        ['owner', '', 3, 'ignore'],
        ['status', '', 3, 'ignore'],
        ['is_connect', '', 3, 'ignore'],
        ['is_media', '', 3, 'ignore']
        );
}