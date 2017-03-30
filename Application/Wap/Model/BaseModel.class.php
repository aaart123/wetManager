<?php

namespace Wap\Model;

use Think\Model;

class BaseModel extends Model{
    


    protected $_auto = array(
        array('create_time','time',1,'function'),
        array('modified_time','time',2,'function'),
    );


}

