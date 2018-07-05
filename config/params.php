<?php

// return [
//     'adminEmail' => 'admin@example.com',
//     'page_size'  => 10,
//     'img'=>'http://p0dhd6dx8.bkt.clouddn.com/2018/2/10/1518236936000.jpg',
//     // 'H5Host'=>'http://h5.dev.muu.pk4yo.com'
// ];
return  [
	 'pc_alipay' => [
        //  签约账号
        'partner' => '2088721600105567',
        //  收款支付宝账号,一般情况下收款账号就是签约账号
        'seller_email' => '851515466@qq.com',

        //商户的私钥,
        'private_key' => 'MIICXAIBAAKBgQDbrazS0Hf7iNFK6dhVCA6dVX087xeAulEndBzZ84Ib1uF+hX+P
0W668Gzuq9nkcIH2Dl1SE/XFvUj9cKEyW29NhM+bfnfySP8PXOHAeQLcocomu5M6
JPHM7i6UEFKvzg85yFEI4lBjxEMkFb9EPle8PYnhaV9E8MEGe65XL9QW+wIDAQAB
AoGAYffbKg0UZRXIGLue4ZT9R4c3mfQarCrrREgREdX6AUZcO9t3XwEMe8v7GJmF
S84s9znCvnbuVWgr4/LVwKpsQPchCnRe1jd53bDvk7WpAvP5MCHCj/8PIcFKsuDm
24y0PQeI2oErqchfnLRnEFeJvSzwkAkk8BrZPy0ouWHtDTECQQD5Pqa6YAhgosGi
BzFfYJ40n303+xZjnPTxAGrvJ++dlKn3xqjs7EjGc47HAQ/QMTiRmFLhjIJES3yY
LwX3F9tJAkEA4aHh18f5RKvTSgs+p7mgBQVHGR0PW9boeEIWANt1yQlajY+w8/q3
nrgtrfAmp+OkRorrMeFFn3mled1Hgsw8IwJATOYjksUmUIpmq5MEjKTGqv26KJdz
ZPB8Mg8q7vanWzsO5b+JRu/v1Cq7FnMhad6F0YXprGUpm+CeZGW9tWrK4QJAb+39
eizitDVE8KNZZp0IC9WNaqDm4Jlg419tSOmVqbAxMq6Iis/iTSNyzamnk3uzH6eE
e08UWcNf2m9yLSmh3wJBAOBopYTcLTCjox2KwKt6hLHIgWFvbgbkLMCFy40i7Vjs
AeDI1w3S32XFcT3VyZ6g8qlpfKkf2mx/dK26k7QRaUg=',
        //支付宝的公钥
        'alipay_public_key' => 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCnxj/9qwVfgoUh/y2W89L6BkRAFljhNhgPdyPuBV64bfQNN1PjbCzkIM6qRdKBoLPXmKKMiFYnkd6rAoprih3/PrQEB/VsW8OoM8fxn67UDYuyBTqA23MML9q1+ilIZwBC2AQ2UBVOrFXfFl75p6/B5KsiNG9zpgmLCUYuLkxpLQIDAQAB',
        // 异步通知页面路径
        'notify_url' => 'http://miraila.dev.pk4yo.com/index.php/pay/notify',

        // 页面跳转同步通知页面路径
        'return_url' => "http://miraila.dev.pk4yo.com/index.php/pay/success",

        //签名方式
        'sign_type' => strtoupper('RSA'),

        //字符编码格式 目前支持 gbk 或 utf-8
        'input_charset' => strtolower('utf-8'),

        //  ca证书路径地址，用于curl中ssl校验
        //  请保证cacert.pem文件在当前文件夹目录中
        'cacert' => getcwd().'\\cacert.pem',

        //  访问模式
        'transport' =>  'http',

        //  支付类型 ，无需修改
        'payment_type' => '1',

        //  产品类型，无需修改
        'service' => "alipay.wap.create.direct.pay.by.user",

        //  以下防钓鱼信息，如果没开通防钓鱼功能，为空即可
        'anti_phishing_key' => '',
        'exter_invoke_ip' => ''
    ],

    'wechat_config' =>[
        'APPID' => 'wx865a30f6140b1c65',
        'MCHID' => '1423348902',
        'KEY' => 'PK4yo20173b9a915079d4e71c4ad57ab',
        'APPSECRET' => '87bd4380adf06e378ea9aaad0e56712b',
        'REDIRECT_URL' => 'http://miraila.dev.pk4yo.com/index.php/pay/wechat',
        'NOTIFY_URL' =>'http://miraila.dev.pk4yo.com/index.php/pay/wnotify'
    ],
    'eth' => '0x171C0aef047F8583b37d81cb3757399b79dFA84a'
];

