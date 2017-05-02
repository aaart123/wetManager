<?php

namespace Datacube\Controller;

use Think\Controller;

use Base\Controller\WetchatApiController  as  Wechat;

class IndexController extends Controller
{

    
    public function user()
    {
        $publicId = $_GET['public_id'];
        $timestamp = $_GET['timestamp'];
        $date = date('Y-m-d',$timestamp);
        $wechat = new Wechat();
        $wechat->publicId = $publicId;
        $data = json_encode([
            'begin_date'=>$date,
            'end_date'=>$date,
        ]);
        $response = $wechat->getUserSummary($data);
        foreach($response['list'] as $value){
            $response1['new_user'] += $value['new_user'];
            $response1['cancel_user'] += $value['cancel_user'];
        }
        $response1['net_user'] = $response1['new_user']-$response1['cancel_user'];
        $response2 = $wechat->getUserCumulate($data);
        $response = array_merge($response1,$response2);
        echo json_encode([
            'errcode'=>0,
            'errmsg'=>$response
        ]);
    }




    public function article()
    {
        $publicId = $_GET['public_id'];
        $timestamp = $_GET['timestamp'];
        $date = date('Y-m-d',$timestamp);
        $wechat = new Wechat();
        $wechat->publicId = $publicId;
        $data = json_encode([
            'begin_date'=>$date,
            'end_date'=>$date,
        ]);
        $response = $wechat->getArticleTotal($data);
        $response = [
            'ref_date'=>$response['list'][0]['ref_date'],
            'title'=>$response['list'][0]['title'],
            'target_user'=>$response['list'][0]['details'][0]['target_user'],
            'int_page_read_user'=>$response['list'][0]['details'][0]['int_page_read_user'],
            'int_page_read_count'=>$response['list'][0]['details'][0]['int_page_read_count'],
            'ori_page_read_user'=>$response['list'][0]['details'][0]['ori_page_read_user'],
            'ori_page_read_count'=>$response['list'][0]['details'][0]['ori_page_read_count'],
            'share_user'=>$response['list'][0]['details'][0]['share_user'],
            'share_count'=>$response['list'][0]['details'][0]['share_count'],
        ];
        echo json_encode([
            'errcode'=>0,
            'errmsg'=>$response
        ]);
    }




    public function interfaceSummary()
    {
        $publicId = $_GET['public_id'];
        $timestamp = $_GET['timestamp'];
        $date = date('Y-m-d',$timestamp);
        $wechat = new Wechat();
        $wechat->publicId = $publicId;
        $data = json_encode([
            'begin_date'=>$date,
            'end_date'=>$date,
        ]);
        $response = $wechat->getInterfaceSummary($data);
        echo json_encode([
            'errcode'=>0,
            'errmsg'=>$response['list'][0],
        ]);
    }






}