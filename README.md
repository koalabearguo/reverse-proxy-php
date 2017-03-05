# reverse-proxy-php
看了几天的php教程，写了个基于GAE的php反向代理，太不专业，但是可以用
因为GAE php默认不支持curl，所以这里使用的是file_get_content，目前支持

1.cookie

2.http头中的cache-control，location字段

3.GET/POST

4.强制使用https

5.客户端agent

6.全文域名替换

.htaccess 是在使用apache主机空间时使用的，空间目录下，除了index.php是默认代理脚本，
还可以放置其他的php脚本，访问的时候按照路径访问就可以了。

app.yaml文件是用于GAE的，只要把app.yaml和index.php部署到GAE空间就可以了。

index.php中的$target_host可以修改为自己反向代理的网址