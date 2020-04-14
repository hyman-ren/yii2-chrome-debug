# Yii2 Chrome Debug

一个可以用Chrome Console 调试Yii代码的工具，相比Yii2自带debug，做到了无HTML污染，且无缓存文件产生。
---
###使用方法：

* 确保你使用的是chrome内核浏览器，推荐Chrome、EDGE

* 运行 composer require hyman-ren/yii2-chrome-debug:dev-master

* 配置Yii2:
```php
   $config['bootstrap'][] = 'debug';
   $config['modules']['debug'] = [
       'class' => \hyman\debug\Module::className(),
   ];
```
* 安装tool目录的chrome扩展，并打开扩展

* 为保证nginx header不会被撑爆，nginx vhost里添加以下代码:
```nginx
        location ~ \.php$ {
            #以下两行需要添加，保证nginx header不会被撑爆
            fastcgi_buffer_size 512k;
            fastcgi_buffers 32 320k;
			#
            fastcgi_pass   127.0.0.1:9000;
```