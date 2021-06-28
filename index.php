<?php
//代理的域名及使用的协议最后不用加/
$target_host="https://www.google.com";
//处理代理的主机得到协议和主机名称
$protocal_host=parse_url($target_host);
//解决中文乱码
#header("Content-Type:text/html;charset=gb2312"); 
//本页面强制开启https
if ($_SERVER["HTTPS"] <> "on")
{
    $xredir="https://".$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	header("HTTP/1.1 301 Moved Permanently");
    header("Location: ".$xredir);
	return;
}
//获取浏览器的的Agent
$Agent = $_SERVER['HTTP_USER_AGENT'];
//以.分割域名字符串
$rootdomain=explode(".",$_SERVER["SERVER_NAME"]);
//获取数组的长度
$lenth=count($rootdomain);
//获取顶级域名
$top=".".$rootdomain[$lenth-1];
//获取主域名
$root=".".$rootdomain[$lenth-2];

//解析url参数
function get_request_params() 
{ 
   $url = $_SERVER["REQUEST_URI"]; 
   
   $refer_url = parse_url($url); 
   
   $params = $refer_url['query']; 
   
   $arr = array(); 
   if(!empty($params)) 
   { 
       $paramsArr = explode('&',$params); 
   
       foreach($paramsArr as $k=>$v) 
       { 
          $a = explode('=',$v); 
          $arr[$a[0]] = $a[1]; 
       } 
   } 
   return $arr; 
}
//解析HTTP响应头
function parse_headers($headers)
{
    //$head = array();
	global $root,$top;
    foreach( $headers as $k=>$v )
    {
        $t = explode( ':', $v, 2 );
        if( isset( $t[1] ) )
		{
			//$head[ trim($t[0]) ] = trim( $t[1] );
			if(strcasecmp('Set-Cookie',trim($t[0]))==0)
			{
				//处理COOkie的domain关键字
				$targetcookie=trim( $t[1] ).";";
				$res_cookie=preg_replace("/domain=.*?;/","domain=".$root.$top.";",$targetcookie);
				$res_cookie=substr($res_cookie,0,strlen($res_cookie)-1); 
				header("Set-Cookie: ".$res_cookie);
			}
			elseif(strcasecmp('Content-Type',trim($t[0]))==0)
			{
				header("Content-Type: ".trim( $t[1] ));
			}
			elseif(strcasecmp('Location',trim( $t[0] ))==0)
			{
				$relocation=str_replace($protocal_host['host'],$_SERVER["SERVER_NAME"],trim( $t[1] ));
				header("Location: ".$relocation);
			}
			elseif(strcasecmp('cache-control',trim( $t[0] ))==0)
			
				header("cache-control: ".trim( $t[1] ));
				
			else
				continue;
		}
        // else
        // {
            // $head[] = $v;
            // if( preg_match( "#HTTP/[0-9\.]+\s+([0-9]+)#",$v, $out ) )
                // $head['reponse_code'] = intval($out[1]);
        // }
    }
    return;// $head;
}
//关系数组转换成字符串，每个键值对中间用=连接，以; 分割
function array_to_str($array)  
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
//debug to console
function debug_to_console($data) {
    if(is_array($data) || is_object($data))
	{
		echo("<script>console.log('PHP: ".json_encode($data)."');</script>");
	} else {
		echo("<script>console.log('PHP: ".$data."');</script>");
	}
}
//组装HTTP请求头
$opts="";
if($_SERVER['REQUEST_METHOD']=='POST') 
{
	$postdata=file_get_contents("php://input");
	$opts = array(
	'http'=>array(
	'method'=>$_SERVER['REQUEST_METHOD'],
	'content'=>$postdata,
    'header'=>"Accept-language: zh-CN\r\n" .   //可以使用客户端浏览器的$_SERVER['HTTP_ACCEPT_LANGUAGE']
			  "user-agent: '$Agent'"."\r\n".
			  "Cookie: ".array_to_str($_COOKIE)."\r\n".
			  //"Accept-Encoding: gzip, deflate, sdch\r\n".
			  "Content-Type: ".$_SERVER['CONTENT_TYPE']
			  
		)
	);
	
}
else
{
	$opts = array(
	'http'=>array(
	'method'=>$_SERVER['REQUEST_METHOD'],
    'header'=>"Accept-language: zh-CN\r\n" .
			  "user-agent: '$Agent'"."\r\n".
			  //"Accept-Encoding: gzip, deflate, sdch\r\n".
			  "Cookie: ".array_to_str($_COOKIE)
		)
	);
	
}
//
$context = stream_context_create($opts);
//发送请求
$new_request_uri = "";
$path_script  = pathinfo($_SERVER["PHP_SELF"]);
//
if ($path_script['dirname']!="/") {
	$new_request_uri = substr_replace($_SERVER["REQUEST_URI"],"",strpos($_SERVER["REQUEST_URI"],$path_script['dirname']),strlen($path_script['dirname']));
} else {
	$new_request_uri = $_SERVER["REQUEST_URI"];
}
$homepage = file_get_contents($protocal_host['scheme']."://".$protocal_host['host'].$new_request_uri,false,$context);
//处理file_get_contents返回的响应求头
parse_headers($http_response_header);
//针对谷歌香港做特殊替换处理
//$homepage=str_replace("www.google.com.hk",$_SERVER["SERVER_NAME"],$homepage);
//替换域名并输出网页
$homepage=str_replace($protocal_host['host'],$_SERVER["SERVER_NAME"],$homepage);
//输出网页内容
echo $homepage;
?>
