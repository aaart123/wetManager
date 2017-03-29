<?php

// 发送http请求
function httpRequest($url, $data = null, $header = array())
{
    $curl = curl_init();
    if (empty($header)) {
        $header = array("content-type: application/x-www-form-urlencoded;charset=UTF-8");
    }
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    if (!empty($data)) {
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curl);
    if ($output === false) {
        echo curl_error($curl);
        curl_close($curl);
        return false;
    }
    curl_close($curl);
    return $output;
}





/**
 * http请求
 * @param unknown $url
 * @param string $data
 * @return mixed
 */
function https_request($url, $data = null)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    if (!empty($data)){
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curl);
    curl_close($curl);
    return $output;
}





/**
 * 输出xml字符
 * @throws WxPayException
**/
function arr2Xml($values)
{
    $xml = "<xml>";
    foreach ($values as $key => $val) {
        if (is_numeric($val)) {
            $xml.="<".$key.">".$val."</".$key.">";
        } else {
            $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
        }
    }
    $xml.="</xml>";
    return $xml;
}
  
/**
 * 将xml转为array
 * @param string $xml
 * @throws WxPayException
 */
function xml2Arr($xml)
{
    //将XML转为array
    $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    return $values;
}

// unicode转utf8
function unicode2Utf8($c)
{
    if ($c < 0x80) {
         $utf8char = chr ( $c );
    } elseif ($c < 0x800) {
         $utf8char = chr (0xC0 | $c >> 0x06). chr (0x80 | $c & 0x3F);
    } elseif ($c < 0x10000) {
         $utf8char = chr (0xE0 | $c >> 0x0C). chr (0x80 | $c >> 0x06 & 0x3F). chr (0x80 | $c & 0x3F);
    } //因为UCS-2只有两字节，所以后面的情况是不可能出现的，这里只是说明unicode HTML实体编码的用法。
    else {
         $utf8char = "&#{$c};" ;
    }
    return $utf8char ;
}

// utf8转unicode
function utf82Unicode($str)
{
    $unicode = array();
    $values = array();
    $lookingFor = 1;
    for ($i = 0; $i < strlen( $str ); $i++) {
        $thisValue = ord( $str[ $i ] );
        if ($thisValue < ord('A')) {
            // exclude 0-9
            if ($thisValue >= ord('0') && $thisValue <= ord('9')) {
                 // number
                 $unicode[] = chr($thisValue);
            } else {
                 $unicode[] = '%'.dechex($thisValue);
            }
        } else {
            if ($thisValue < 128) {
                $unicode[] = $str[ $i ];
            } else {
                if (count( $values ) == 0) {
                    $lookingFor = ( $thisValue < 224 ) ? 2 : 3;
                }
                $values[] = $thisValue;
                if (count( $values ) == $lookingFor) {
                    $number = ( $lookingFor == 3 ) ?
                        ( ( $values[0] % 16 ) * 4096 ) + ( ( $values[1] % 64 ) * 64 ) + ( $values[2] % 64 ):
                        ( ( $values[0] % 32 ) * 64 ) + ( $values[1] % 64 );
                    $number = dechex($number);
                    $unicode[] = (strlen($number)==3)?"\u0".$number:"\u".$number;
                    $values = array();
                    $lookingFor = 1;
                } // if
            } // if
        }
    } // for
    return implode("", $unicode);
}


/**
    * 随机生成16位字符串
    * @return string 生成的字符串
    */
function getRandomStr()
{
    $str = "";
    $str_pol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
    $max = strlen($str_pol) - 1;
    for ($i = 0; $i < 16; $i++) {
        $str .= $str_pol[mt_rand(0, $max)];
    }
    return $str;
}


/**
 * 上传base64位图片
 */
function base64Img($base, $rootPath)
{
    $cfg['rootPath'] = $rootPath;
    $upload = new \Org\Util\UploadBase64($cfg);
    $info = $upload->upload($base);
    if (empty($info)) {
        return false;
    } else {
        $info = substr($info, 1);
        return $info;
    }
}