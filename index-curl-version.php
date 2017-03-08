<?php
//
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

//解析返回头
function parse_curl_headers($header_text)
{
    $headers = array();
	global $root,$top;
    //$header_text = substr($response, 0, strpos($response, "\r\n\r\n"));
    foreach (explode("\r\n", $header_text) as $i => $line)
        if ($i === 0)
            $headers['http_code'] = $line;
        else
        {
            list ($key, $value) = explode(':', $line);

            //$headers[$key] = $value;
			if(strcasecmp('Set-Cookie',trim($key))==0)
			{
				//处理COOkie的domain关键字
				$targetcookie=trim( $value ).";";
				$res_cookie=preg_replace("/domain=.*?;/","domain=".$root.$top.";",$targetcookie);
				$res_cookie=substr($res_cookie,0,strlen($res_cookie)-1); 
				header("Set-Cookie: ".$res_cookie);
			}
			elseif(strcasecmp('Content-Type',trim($key))==0)
			{
				header("Content-Type: ".trim( $value ));
			}
			/* elseif(strcasecmp('Location',trim( $key ))==0)
			{
				$relocation=str_replace($protocal_host['host'],$_SERVER["SERVER_NAME"],trim( $value ));
				header("Location: ".$relocation);
				//continue;
				//echo $relocation;
			} */
			elseif(strcasecmp('cache-control',trim( $key ))==0)
			
				header("cache-control: ".trim( $value ));
				
			else
				continue;
        }

    return $headers;
}
//组装http头
$headers = array();
$headers[]="Accept-language: zh-CN";
$headers[]="Cookie: ".array_to_str($_COOKIE);

$ch="";
function curl_file_get_contents($url,$post=false){
	global $headers,$ch;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    //post数据
    if($post==true)
    {
		$headers[]="Content-Type: ".$_SERVER['CONTENT_TYPE'];
        curl_setopt($ch, CURLOPT_POST,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents("php://input"));
		//print_r(http_build_query($_POST));
    }
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    //curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    $data = curl_exec($ch);
	
	// Send the header based on the response from the server
	//header("Content-type: ".curl_getinfo($ch, CURLINFO_CONTENT_TYPE));
	//curl_close($ch);
    return $data;
}

// create a new curl resource and set options
if($_SERVER['REQUEST_METHOD']=='POST')
	$output=curl_file_get_contents($protocal_host['scheme']."://".$protocal_host['host'].$_SERVER["REQUEST_URI"],true);
else
	$output=curl_file_get_contents($protocal_host['scheme']."://".$protocal_host['host'].$_SERVER["REQUEST_URI"],false);

//分离头部和内容
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$result_headers = substr($output, 0, $headerSize);
//print_r($result_headers);
$results = substr($output, $headerSize);
//print_r($result_headers);
parse_curl_headers($result_headers);
// Send the curl output
$output=str_replace($protocal_host['host'],$_SERVER["SERVER_NAME"],$results);
echo $output;
// close curl resource
curl_close($ch);

?> 