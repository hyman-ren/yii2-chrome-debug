# Yii2 Chrome Debug


&nbsp;&nbsp;&nbsp;&nbsp;Yii2 Chrome Debug是一个Yii2代码的高效调试工具，提供安全的传输方式，可以用于生产环境进行安全调试。 


# 相比Yii2自己debug，有以下优点：

* 提供安全的传输方式，支持访问量不是太大的站点生产环境测试（采用AES对称加密，保证性能与安全）；
* 无html注入，不影响网站原来显示效果；
* 对不同模块产生的debug记录进行了分层折叠，让你对页面执行流程和模板层级关系一目了然，方便快速定位问题；
* 支持ajax请求debug；
* 做到了无HTML污染，不影响页面展示；
* 无缓存文件产生，无IO操作，理论上速度更快；
* 在console Debug，可以不用离开当前页面,Debug更容易。


#  使用方法：

*  确保你使用的是chrome内核浏览器，推荐Chrome、EDGE

*  运行 composer require hyman-ren/yii2-chrome-debug:dev-master

*  修改配置:

&nbsp;&nbsp;&nbsp;&nbsp; 修改Yii2配置：文件main-local.php，修改内容如下

```php

   $config['bootstrap'][] = 'debug';
   $config['modules']['debug'] = [
        'class' => \hyman\debug\Module::className(),
        //autoFolding 选填，内部分组是否自动折叠，默认不折叠
        'autoFolding' => true,

        //encryptType选填，加密类型，目前支持两种 aes和base64，默认base64，
        //开发环境建议base64，生产环境建议aes，
        //选中aes  aesKey aesIv两个参数必填，且必须16位，只支持英文字母、数字
        'encryptType' => 'aes',  

        //aesKey 选填，加密的秘钥，加密类型为aes时候必填，必须16位，只支持英文字母、数字，修改后需要同步修改扩展包中对应的值
        'aesKey' => '1234567890123456',

        //aesIv 选填 加密的初始化向量，加密类型为aes时候必填，必须16位，只支持英文字母、数字，修改后需要同步修改扩展包中对应的值
        'aesIv'  => '1234567890123456',
   ];
	return $config;

```


&nbsp;&nbsp;&nbsp;&nbsp;修改扩展包配置：如果aesKey、aesIv有修改，扩展包同步修改，base64加密忽略此步骤，文件 log.js

```javaScript

    var AES_IV  = '1234567890123456';
    var AES_KEY = '1234567890123456';

```

* 安装tool目录的chrome扩展包，并打开扩展包，点亮调试网站的调试图标。

* 修改nginx配置：为保证nginx header不会被撑爆，nginx vhost里添加以下代码:

```nginx

        location ~ \.php$ {
            #以下两行需要添加，保证nginx header不会被撑爆
            fastcgi_buffer_size 512k;
            fastcgi_buffers 32 320k;
			#
            fastcgi_pass   127.0.0.1:9000;

```



