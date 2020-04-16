# Yii2 Chrome Debug

---

&nbsp;&nbsp;&nbsp;&nbsp;Yii2 Chrome Debug是一个可以用Chrome Console 调试Yii代码的工具。

### 相比Yii2自己debug，有以下优点：

* 支持访问量不是太大的站点生产环境测试（为保证安全性，特支持了AES对称加密）；
* 对不同模块产生的debug记录进行了分层折叠，让你对页面执行流程和模板层级关系一目了然，方便快速定位问题；
* 支持ajax请求debug；
* 做到了无HTML污染，不影响页面展示；
* 无缓存文件产生，无IO操作，理论上速度更快；
* 在console Debug，可以不用离开当前页面,Debug更容易。

---

### 使用方法：

* 确保你使用的是chrome内核浏览器，推荐Chrome、EDGE

* 运行 composer require hyman-ren/yii2-chrome-debug:dev-master

* 配置Yii2:
&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;无AES加密配置（开发环境推荐）文件 main-local.php

```php

   $config['bootstrap'][] = 'debug';
   $config['modules']['debug'] = [
       'class' => \hyman\debug\Module::className(),
   ];
	return $config;
```

&nbsp;&nbsp;&nbsp;&nbsp;AES加密配置（开发环境推荐）,秘钥可以手动修改，注意：必须是16位的，且需要同步修改扩展包中的秘钥
&nbsp;&nbsp;&nbsp;&nbsp;PHP修改内容，文件 main-local.php

```php

    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => \hyman\debug\Module::className(),
        'encryptType' => 'aes',
        'aesKey' => '1234567890123456',
        'aesIv'  => '1234567890123456',
    ];
	return $config;

```

&nbsp;&nbsp;&nbsp;&nbsp;扩展包修改内容，文件 log.js

```javaScript
    var AES_IV  = '1234567890123456';
    var AES_KEY = '1234567890123456';

```

* 安装tool目录的chrome扩展包，并打开

* 为保证nginx header不会被撑爆，nginx vhost里添加以下代码:

```nginx
        location ~ \.php$ {
            #以下两行需要添加，保证nginx header不会被撑爆
            fastcgi_buffer_size 512k;
            fastcgi_buffers 32 320k;
			#
            fastcgi_pass   127.0.0.1:9000;
```



