<?php
namespace Wap\Model;

use Think\Model\ViewModel;

class PublicSubscribeViewModel extends ViewModel
{
    public $viewFields = array(
        'Subscribe' => [
            '_table' => 'kdgx_public_subscribe',
            '_as' => 's',
            'public_id'
        ],
        '' => [
            '_table' => 'kdgx_wap_public',
            '_as' => 'p',
            '_on' => 's.public_id=p.user_name',
            'user_name',
            'nick_name',
            'head_img',
            'qrcode_url',
            'fans'
        ]
    );

}