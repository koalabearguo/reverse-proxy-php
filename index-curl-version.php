<?php 

$target_host="https://www.google.com/";

//处理代理的主机得到协议和主机名称

$protocal_host=parse_url($target_host);

//以.分割域名字符串
$rootdomain=explode(".",$_SERVER["SERVER_NAME"]);
//获取数组的长度
$lenth=count($rootdomain);
//获取顶级域名
$top=".".$rootdomain[$lenth-1];
//获取主域名
$root=".".$rootdomain[$lenth-2];

$aAccess = curl_init() ;
// --------------------

// set URL and other appropriate options

curl_setopt($aAccess, CURLOPT_URL,$protocal_host['scheme']."://".$protocal_host['host'].$_SERVER["REQUEST_URI"]);

curl_setopt($aAccess, CURLOPT_HEADER, true);
curl_setopt($aAccess, CURLOPT_RETURNTRANSFER, true);
curl_setopt($aAccess, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($aAccess, CURLOPT_SSL_VERIFYPEER, false);  
curl_setopt($aAccess, CURLOPT_SSL_VERIFYHOST, false);  
curl_setopt($aAccess, CURLOPT_TIMEOUT, 60);
curl_setopt($aAccess, CURLOPT_BINARYTRANSFER, true);

//if(!empty($_SERVER['HTTP_REFERER']))
    //curl_setopt($aAccess,CURLOPT_REFERER,$_SERVER['HTTP_REFERER']) ;

//关系数组转换成字符串，每个键值对中间用=连接，以; 分割
function array_to_str ($array)  
{  
   $string="";
    if (is_array($array)) 
	{  
        foreach ($array as $key => $value) 
		{   
			if(!empty($string))
				$string.="; ".$key."=".$value;
			else
				$string.=$key."=".$value;
        }   
    } else 
	{  
            $string = $array;  
    }      
    return $string;  
} 

//$headers=get_client_header();
$headers = array();
$headers[]="Accept-language: zh-CN,zh";//$_SERVER['HTTP_ACCEPT_LANGUAGE'];
$headers[]="Cookie: ".array_to_str($_COOKIE);
$headers[]="user-agent: ".$_SERVER['HTTP_USER_AGENT'];


if( $_SERVER['REQUEST_METHOD']=='POST' )
{
	$headers[]="Content-Type: ".$_SERVER['CONTENT_TYPE'];
	curl_setopt($aAccess, CURLOPT_POST, 1);
	curl_setopt($aAccess, CURLOPT_POSTFIELDS, http_build_query($_POST));
}

curl_setopt($aAccess,CURLOPT_HTTPHEADER,$headers) ;
// grab URL and pass it to the browser



$sResponse = curl_exec($aAccess);
list($headerstr,$sResponse)=parse_header($sResponse);
$headarr= explode("\r\n", $headerstr);
foreach($headarr as $h){
	if(strlen($h)>0){
		if(strpos($h,'Content-Length')!==false) continue;
		if(strpos($h,'Transfer-Encoding')!==false) continue;
		if(strpos($h,'Connection')!==false) continue;
		if(strpos($h,'HTTP/1.1 100 Continue')!==false) continue;
		if(strpos($h,'Set-Cookie')!==false) 
		{
			$targetcookie=$h.";";
			$res_cookie=preg_replace("/domain=.*?;/","domain=".$root.$top.";",$targetcookie);
			$h=substr($res_cookie,0,strlen($res_cookie)-1);
		}
		header($h);
	}
}

function get_client_header(){
	$headers=array();
	foreach($_SERVER as $k=>$v){
		if(strpos($k,'HTTP_')===0){
			$k=strtolower(preg_replace('/^HTTP/', '', $k));
			$k=preg_replace_callback('/_\w/','header_callback',$k);
			$k=preg_replace('/^_/','',$k);
			$k=str_replace('_','-',$k);
			if($k=='Host') continue;
			$headers[]="$k: $v";
		}
	}
	return $headers;
}

function header_callback($str){
	return strtoupper($str[0]);
}

function parse_header($sResponse){
	list($headerstr,$sResponse)=explode("\r\n\r\n",$sResponse, 2);
	$ret=array($headerstr,$sResponse);
	if(preg_match('/^HTTP\/1\.1 \d{3}/', $sResponse)){
		$ret=parse_header($sResponse);
	}
	return $ret;
}


// close cURL resource, and free up system resources
$sResponse=str_replace($protocal_host['host'],$_SERVER["SERVER_NAME"],$sResponse);

curl_close($aAccess);

echo $sResponse ;
?>