<?php
/**
 * Created by polynds.
 * Date: 2018/7/21
 * Time: 10:06
 */

/**
 * 计算两个Unix时间戳之间相隔的年月日
 * @param $date1
 * @param $date2
 * @return array
 */
function getDiffDate($date1, $date2) {
    if (strtotime($date1) > strtotime($date2)) {
        $ymd = $date2;
        $date2 = $date1;
        $date1 = $ymd;
    }
    list($y1, $m1, $d1) = explode('-', $date1);
    list($y2, $m2, $d2) = explode('-', $date2);
    $y = $m = $d = $_m = 0;
    $math = ($y2 - $y1) * 12 + $m2 - $m1;
    $y = round($math / 12);
    $m = intval($math % 12);
    $d = (mktime(0, 0, 0, $m2, $d2, $y2) - mktime(0, 0, 0, $m2, $d1, $y2)) / 86400;
    if ($d < 0) {
        $m -= 1;
        $d += date('j', mktime(0, 0, 0, $m2, 0, $y2));
    }
    $m < 0 && $y -= 1;
    return array($y, $m, $d);
}


/**
 * 数组转xml
 * @param $arr
 * @param null $dom
 * @param null $node
 * @param string $root
 * @param bool $cdata
 * @return string
 */
function arrayToXml($arr,$dom=null,$node=null,$root='xml',$cdata=false){
    if (!$dom){
        $dom = new DOMDocument('1.0','GBK');
    }
    if(!$node){
        $node = $dom->createElement($root);
        $dom->appendChild($node);
    }
    foreach ($arr as $key=>$value){
        $child_node = $dom->createElement(is_string($key) ? $key : 'trems');
        $node->appendChild($child_node);
        if (!is_array($value)){
            if (!$cdata) {
                $data = $dom->createTextNode($value);
            }else{
                $data = $dom->createCDATASection($value);
            }
            $child_node->appendChild($data);
        }else {
            arrayToXml($value,$dom,$child_node,$root,$cdata);
        }
    }
    return $dom->saveXML();
}

/**
 * post请求
 * @param $xmldata
 * @param $url
 * @return mixed
 */
function sendPost($xmldata,$url){
    $curl = curl_init();
    //设置url
    curl_setopt($curl, CURLOPT_URL,$url);//测试
    //设置发送方式：post
    curl_setopt($curl, CURLOPT_POST, true);
    //设置发送数据
    curl_setopt($curl, CURLOPT_POSTFIELDS, $xmldata);

    //TRUE 将curl_exec()获取的信息以字符串返回，而不是直接输出
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    //执行cURL会话 ( 返回的数据为xml )
    $return_xml = curl_exec($curl);

    //关闭cURL资源，并且释放系统资源
    curl_close($curl);
    //禁止引用外部xml实体
    libxml_disable_entity_loader(true);
    //先把xml转换为simplexml对象，再把simplexml对象转换成 json，再将 json 转换成数组。
    $value_array = json_decode(json_encode(simplexml_load_string(urldecode($return_xml), 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    return $value_array;
}




/** 判断OS
 * @param $str
 * @return mixed
 */
function getOS()
{
    $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
    if(strpos($agent, 'windows nt')) {
        $platform = 'windows';
    } elseif(strpos($agent, 'macintosh')) {
        $platform = 'mac';
    } elseif(strpos($agent, 'ipod')) {
        $platform = 'ipod';
    } elseif(strpos($agent, 'ipad')) {
        $platform = 'ipad';
    } elseif(strpos($agent, 'iphone')) {
        $platform = 'iphone';
    } elseif (strpos($agent, 'android')) {
        $platform = 'android';
    } elseif(strpos($agent, 'unix')) {
        $platform = 'unix';
    } elseif(strpos($agent, 'linux')) {
        $platform = 'linux';
    } else {
        $platform = 'other';
    }
    return $platform;
}

/** 获取请求来源哪个平台
 * @return string
 */
function getSource()
{
    $res = getOS();
    if ($res == 'android') {
        return 'android';
    } elseif ($res == 'iphone' || $res == 'ipad') {
        return 'ios';
    } else {
        return 'wap';
    }
}


/** 获取android APP 版本号
 */
function getAppVersion($name){
    if (preg_match('/'.$name.'\/(\d[\d.]+\d)/', $_SERVER['HTTP_USER_AGENT'], $res))
        return $res[1];
    else
        return null;
}

/**
 * 验证手机号码格式正确性
 * @param unknown $mobile
 * @return boolean
 */
function isMobile($mobile){
    $pattern = "/^(13|14|15|17|18)\d{9}$/";
    return preg_match($pattern, $mobile);
}

/**
 * 获取客户端ip
 */
function getIp()
{
    if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
        $cip = $_SERVER["HTTP_CLIENT_IP"];
    } elseif (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
        $cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
    } elseif (!empty($_SERVER["REMOTE_ADDR"])) {
        $cip = $_SERVER["REMOTE_ADDR"];
    } else {
        $cip = "";
    }
    return $cip;
}

/**
 * 类似range
 * @param $v
 * @param int $step
 * @return array
 *
 */
function array_creates($v,$step=1){
    $res = array();
    for ($i=0;$i<$step;$i++){
        $res[] = $v;
    }
    return $res;
}

//-----------------------------------------------多单位数量计算相应值---------------------------------------------------------------------

//按照单位计算总库存
function CalculateStockForUnit ($stockNum1,$stockNum2,$stockNum3,$specNum2,$specNum3){
//按照单位和相应的库存数计算的总库存，单位之间是递进关系，比如一吨=10箱=10瓶
    $stock = 0;
    if($specNum2!=0){
        $stock+=bcdiv($stockNum2,$specNum2,'2');
    }
    if($specNum3!=0 && $specNum2!=0){
        $stock+=bcdiv(bcdiv($stockNum3,$specNum2,'2'),$specNum3,'2');
    }
    $stock+=$stockNum1;
    return ($stock);
}

function unCalculateStockForUnit ($stockNum1=0,$stockNum2=0,$stockNum3=0,$specNum2=0,$specNum3=0,$specNum1=1){
//转化为最小单位的数量
    $stock = 0;
    if($specNum3!=0){//商品有三个单位
        if($stockNum1!=0){
            $stock+=$stockNum1*$specNum2*$specNum3;
        }
        if($stockNum2!=0){
            $stock+=$stockNum2*$specNum3;
        }
        if($stockNum3!=0){
            $stock+=$stockNum3;
        }
    }else if($specNum2!=0){//商品有两个单位
        if($stockNum1!=0){
            $stock+=$stockNum1*$specNum2;
        }
        if($stockNum2!=0){
            $stock+=$stockNum2;
        }
    }else{//商品一定有一个主单位
        $stock = $stockNum1*$specNum1;
    }
    return stockfloorval($stock);//round
}


function ChangeToUnit ($unCalculateStockForUnitres=0,$specNum2=0,$specNum3=0,$specNum1=1){
//将换算后的结果换算成对应的各个单位的库存，返回值可能是数组也可能是数字
    $stock = array();
    $sc = 0;
    if($unCalculateStockForUnitres!=0){
        if($specNum3!=0){
            $stock[] = $unCalculateStockForUnitres%$specNum3;
            $sc = intval($unCalculateStockForUnitres/$specNum3);
            $stock[] = $sc%$specNum2;
            $stock[] = intval($sc/$specNum2);
        }else if($specNum2!=0){
            $sc = ($unCalculateStockForUnitres%$specNum2);//吨
            $stock[] = $sc;
            $stock[] = $unCalculateStockForUnitres/$specNum2;
        }else{
            $stock = $unCalculateStockForUnitres;
        }
    }
    $total = empty($stock)?0:$stock;
    if(is_array($total)){
        $total = array_reverse($total);
        $total  =array_map(function (&$val){
            return floor($val);
        },$total);
    }else{
        $total = stockfloorval($total);
    }
    return $total;
}

function stockfloorval($val){
    return floor($val*100)/100;
}

function SubStockByUnit($prooutam=0,$prooutamlevel=1,$stockNum1=0,$stockNum2=0,$stockNum3=0,$specNum2=0,$specNum3=0,$specNum1=1){
    //减库存
    //$prooutam 是购买数量，$prooutamlevel 是描述购买数量的单位级别
    //$stockNum1---$specNum3是当前库存数量和单位进制数量

    $curr_stocksubminmun = 0;//当前购买库存转化的最小单位数量

    $curr_stockminmun = unCalculateStockForUnit($stockNum1,$stockNum2,$stockNum3,$specNum2,$specNum3);//当前库存转化的最小单位数量
    if($prooutamlevel==1){
        $curr_stocksubminmun = unCalculateStockForUnit($prooutam,0,0,$specNum2,$specNum3);//当前购买库存转化的最小单位数量
    }
    if($prooutamlevel==2){
        $curr_stocksubminmun = unCalculateStockForUnit(0,$prooutam,0,$specNum2,$specNum3);//当前购买库存转化的最小单位数量
    }
    if($prooutamlevel==3){
        $curr_stocksubminmun = unCalculateStockForUnit(0,0,$prooutam,$specNum2,$specNum3);//当前购买库存转化的最小单位数量
    }

    $unCalculateStockForUnitres = bcsub($curr_stockminmun,$curr_stocksubminmun,2);

    return ChangeToUnit($unCalculateStockForUnitres,$specNum2,$specNum3);

}

//转换库存
function addStockByUnit($stockNum1=0,$stockNum2=0,$stockNum3=0,$specNum2=0,$specNum3=0,$specNum1=1){
    $curr_stockminmun = unCalculateStockForUnit($stockNum1,$stockNum2,$stockNum3,$specNum2,$specNum3);//当前库存转化的最小单位数量
    return ChangeToUnit($curr_stockminmun,$specNum2,$specNum3);
}

//-----------------------------------------------多单位数量计算相应值---------------------------------------------------------------------
