<?php
/** 产品密钥ID，产品标识 */
define("SECRETID", "your_secret_id");
/** 产品私有密钥，服务端生成签名信息使用，请严格保管，避免泄露 */
define("SECRETKEY", "your_secret_key");
/** 业务ID，易盾根据产品业务特点分配 */
define("BUSINESSID", "your_business_id");
/** 易盾反垃圾云服务直播视频查询接口地址 */
define("API_URL", "https://as.dun.163yun.com/v1/livevideo/query/task");
/** api version */
define("VERSION", "v1");
/** API timeout*/
define("API_TIMEOUT", 10);
/** php内部使用的字符串编码 */
define("INTERNAL_STRING_CHARSET", "auto");

/**
 * 计算参数签名
 * $params 请求参数
 * $secretKey secretKey
 */
function gen_signature($secretKey, $params){
	ksort($params);
	$buff="";
	foreach($params as $key=>$value){
	     if($value !== null) {
	        $buff .=$key;
		$buff .=$value;
    	     }
	}
	$buff .= $secretKey;
	return md5($buff);
}

/**
 * 将输入数据的编码统一转换成utf8
 * @params 输入的参数
 */
function toUtf8($params){
	$utf8s = array();
    foreach ($params as $key => $value) {
    	$utf8s[$key] = is_string($value) ? mb_convert_encoding($value, "utf8", INTERNAL_STRING_CHARSET) : $value;
    }
    return $utf8s;
}

/**
 * 反垃圾请求接口简单封装
 * $params 请求参数
 */
function check($params){
	$params["secretId"] = SECRETID;
	$params["businessId"] = BUSINESSID;
	$params["version"] = VERSION;
	$params["timestamp"] = time() * 1000;// time in milliseconds
	$params["nonce"] = sprintf("%d", rand()); // random int

	$params = toUtf8($params);
	$params["signature"] = gen_signature(SECRETKEY, $params);
	// var_dump($params);

	$options = array(
	    "http" => array(
	        "header"  => "Content-type: application/x-www-form-urlencoded\r\n",
	        "method"  => "POST",
	        "timeout" => API_TIMEOUT, // read timeout in seconds
	        "content" => http_build_query($params),
	    ),
	);
	$context  = stream_context_create($options);
	$result = file_get_contents(API_URL, false, $context);
	// var_dump($result);
	if($result === FALSE){
		return array("code"=>500, "msg"=>"file_get_contents failed.");
	}else{
		return json_decode($result, true);	
	}
}

// 简单测试
function main(){
    echo "mb_internal_encoding=".mb_internal_encoding()."\n";
	$taskIds = array("202b1d65f5854cecadcb24382b681c1a","0f0345933b05489c9b60635b0c8cc721");
	$params = array(
		"taskIds"=>json_encode($taskIds)
	);
	var_dump($params);

	$ret = check($params);
	var_dump($ret);
	if ($ret["code"] == 200) {
		$result = $ret["result"];
		// var_dump($array);
		foreach($result as $index => $live_ret){
		    // 直播视频唯一id
		    $taskId = $live_ret["taskId"];
		    // 直播状态, 101:直播中，102：直播结束
		    $status = $live_ret["status"];
		    // 回调标识
		    $callback = $live_ret["callback"];
		    // 直播检测状态(0:检测成功，10：检测中，110：请求重复，120：参数错误，130：解析错误，140：数据类型错误，150：并发超限)
		    $callbackStatus = $live_ret["callbackStatus"];
		    // 过期状态（20:直播不是七天内的数据，30：直播taskId不存在）
		    $expireStatus = $live_ret["expireStatus"];
		}
    }else{
    	var_dump($ret);
    }
}
main();
?>