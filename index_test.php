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
//
//header('Content-type: text/html; charset=UTF-8');
//
$aAccess = curl_init() ;
// --------------------

// set URL and other appropriate options
//
$next_domain_url=$_GET["next_domain_url"];
//echo $next_domain_url;
if (empty($next_domain_url))
{
	curl_setopt($aAccess, CURLOPT_URL,$protocal_host['scheme']."://".$protocal_host['host'].$_SERVER["REQUEST_URI"]);
	//debug_to_console($protocal_host['scheme']."://".$protocal_host['host'].$_SERVER["REQUEST_URI"]);
}
else
{
	$spec_url=explode('/',$next_domain_url);
	$next_common_url=base64_decode($spec_url[0]);
	//
	$req_all_param=$_SERVER["REQUEST_URI"];
	//debug_to_console("req_all_param:".$req_all_param);
	$req_ori_param=preg_replace("/\/index.php\?next_domain_url=.*?\//","",$req_all_param);
	//debug_to_console("req_ori_param:".$req_ori_param);
	//
    $next_full_url=$next_common_url."/".$req_ori_param;
	curl_setopt($aAccess, CURLOPT_URL,$next_full_url);
	//
	//debug_to_console("next_full_url:".$next_full_url);
}
//$cookie_jar = tempnam('/tmp','koalabear');
//curl_setopt($aAccess, CURLOPT_COOKIEJAR, $cookie_jar);
//curl_setopt($aAccess, CURLOPT_COOKIE, $cookie_jar);
curl_setopt($aAccess, CURLOPT_HEADER, true);
curl_setopt($aAccess, CURLOPT_RETURNTRANSFER, true);
curl_setopt($aAccess, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($aAccess, CURLOPT_SSL_VERIFYPEER, false);  
curl_setopt($aAccess, CURLOPT_SSL_VERIFYHOST, false);  
curl_setopt($aAccess, CURLOPT_TIMEOUT, 60);
curl_setopt($aAccess, CURLOPT_BINARYTRANSFER, true);
curl_setopt($aAccess, CURLOPT_COOKIESESSION, true);
//curl_setopt($aAccess, CURLOPT_ENCODING, "UTF-8");


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
$headers[]="Accept-language: ".$_SERVER['HTTP_ACCEPT_LANGUAGE'];//$_SERVER['HTTP_ACCEPT_LANGUAGE'];//zh-CN,zh
$headers[]="Cookie: ".$_SERVER['HTTP_COOKIE'];//array_to_str($_COOKIE);
//
//when proxy google,this should be special,or can not access google,there are some browser security issues...
//so far i don't know how to process.
//$headers[]="user-agent: Php_client/1.0";//.$_SERVER['HTTP_USER_AGENT'];
$headers[]="Host: ".$protocal_host['host'];
$headers[]="Referer: ".$protocal_host['scheme']."://".$protocal_host['host'];

//print_r($headers);

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
//
function get_client_header(){
	$headers=array();
	foreach($_SERVER as $k=>$v){
		if(strpos($k,'HTTP_')===0){
			$k=strtolower(preg_replace('/^HTTP/', '', $k));
			$k=preg_replace_callback('/_\w/','header_callback',$k);
			$k=preg_replace('/^_/','',$k);
			$k=str_replace('_','-',$k);
			if($k=='Host')
			{
				$headers[]="$k: ".$protocal_host['host'];
				continue;
			}
			if($k=='Referer')
			{
				$headers[]="$k: ".$protocal_host['host'];
				continue;
			}
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
//debug to console
function debug_to_console_old($data) {
    if(is_array($data) || is_object($data))
	{
		echo("<script>console.log('PHP: ".json_encode($data)."');</script>");
	} else {
		echo("<script>console.log('PHP: ".$data."');</script>");
	}
}
function debug_to_console($data) {
    $output = $data;
    if (is_array($output))
        $output = implode(',', $output);

    echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
}
// close cURL resource, and free up system resources
//
$sResponse=str_replace($protocal_host['host'],$_SERVER["SERVER_NAME"],$sResponse);
//special replace
//$sResponse=str_replace("//google.com","//localhost",$sResponse);
//$sResponse=str_replace("consent.google.com?","consent.google.com/?",$sResponse);
//$sResponse=str_replace("https://consent.google.com","/index.php?next_domain_url=".base64_encode("https://consent.google.com"),$sResponse);
//$sResponse=str_replace("https://ogs.google.com","/index.php?next_domain_url=".base64_encode("https://ogs.google.com"),$sResponse);

//文本转码
$content_mime=curl_getinfo($aAccess,CURLINFO_CONTENT_TYPE);
/*
if(strlen($content_mime)>0)
{
	if(strpos($content_mime,'text/html')!==false)
	{
		$charlen = stripos($sResponse, "charset");
		if (stristr(substr($sResponse, $charlen, 18) , "GBK") || stristr(substr($sResponse, $charlen, 18) , "GB2312")) {
			$sResponse = mb_convert_encoding($sResponse, "UTF-8", "GBK,GB2312,BIG5");
		}
		
	}
}*/

//
curl_close($aAccess);

//search other link in this content
preg_match_all('/((http|https):\/\/([\w\d\-_]+[\.\w\d\-_]+))([\/]?)/i',$sResponse,$m);
$need_replace=$m[1];

//replace other link to base64_encode
/*
foreach ($need_replace as $val) {
	$sResponse=str_replace($val,"/index.php?next_domain_url=".base64_encode($val),$sResponse);
}*/

//output
$headarr= explode("\r\n", $headerstr);
$debug="";
foreach($headarr as $h){
	if(strlen($h)>0){
		if(strpos($h,'Content-Length')!==false) continue;
		if(strpos($h,'Transfer-Encoding')!==false) continue;
		if(strpos($h,'Connection')!==false) continue;
		if(strpos($h,'HTTP/1.1 100 Continue')!==false) continue;
		//
		if(strpos($h,'Content-Security-Policy')!==false) continue;
		if(strpos($h,'Content-Security-Policy-Report-Only')!==false) continue;
		if(strpos($h,'Clear-Site-Data')!==false) continue;
		//
		if(strpos($h,'Set-Cookie')!==false) 
		{
			$targetcookie=$h.";";
			$res_cookie=preg_replace("/domain=.*?;/","domain=".$root.$top.";",$targetcookie);
			$h=substr($res_cookie,0,strlen($res_cookie)-1);
			$debug=$debug.$h;
		}
		header($h);
	}
}
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");

echo $sResponse;
//debug_to_console("hello,world");
//debug_to_console($headarr);
//debug_to_console($debug);
//
?>