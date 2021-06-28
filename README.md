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

index.php中的$target_host可以修改为自己反向代理的网址。

index-curl-version.php是使用的curl实现的代理，功能基本一样。

# 好久没用了 ,发现google已经不好代理了。。。各种策略限制。。。测试中发现不要告诉谷歌自己的agent就会好很多。。。

# 关于index.php放到二级目录,最新的文件已经支持
1. 需要修改.htaccess中这一行，比如二级目录是proxy
```
RewriteRule . /index.php [L]
#修改为:
RewriteRule . /proxy/index.php [L]
```
2. nginx空间我没有试过，需要自己研究重定向的问题
3. GAE空间早就不能用了，未测试

# apache php空间：   

https://koalabear.tk

