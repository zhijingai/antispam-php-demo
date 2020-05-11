<?php
/** 网站检测解决方案结果获取接口 */
/** 产品密钥ID，产品标识 */
define("SECRETID", "your_secret_id");
/** 产品私有密钥，服务端生成签名信息使用，请严格保管，避免泄露 */
define("SECRETKEY", "your_secret_key");
/** 易盾反垃圾云服务网站检测解决方案结果获取接口地址 */
define("API_URL", "http://as.dun.163yun.com/v1/crawler/callback/results");
/** api version */
define("VERSION", "v1.0");
/** API timeout*/
define("API_TIMEOUT", 10);
require("../util.php");

/**
 * 反垃圾请求接口简单封装
 * $params 请求参数
 */
function check(){
    $params = array();
	$params["secretId"] = SECRETID;
	$params["version"] = VERSION;
	$params["timestamp"] = time() * 1000;// time in milliseconds
	$params["nonce"] = sprintf("%d", rand()); // random int

	$params = toUtf8($params);
	$params["signature"] = gen_signature(SECRETKEY, $params);
	// var_dump($params);

	$result = curl_post($params, API_URL, API_TIMEOUT);
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
	$ret = check();
	var_dump($ret);

	if ($ret["code"] == 200) {
		$result_array = $ret["result"];
        foreach($result_array as $res_index => $resultInfo){
            $taskId = $resultInfo["taskId"];
            $dataId = $resultInfo["dataId"];
            $result = $resultInfo["result"];
            $callback = $resultInfo["callback"];
            // 证据信息
            $evidencesObject = $resultInfo["evidences"];
            echo "网站检测解决方案结果：taskId={$taskId}：dataId={$dataId}：result={$result}：callback={$callback}";
        }
    }else{
    	var_dump($ret);
    }
}

main();
?>
