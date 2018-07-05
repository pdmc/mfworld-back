<?php
/* *
 * 配置文件
 * 版本：1.0
 * 日期：2016-06-06
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。
*/
//2017-06-30 db
$alipay_config['seller_email']  = '851515466@qq.com';
 
//↓↓↓↓↓↓↓↓↓↓请在这里配置您的基本信息↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
//合作身份者ID，签约账号，以2088开头由16位纯数字组成的字符串，查看地址：https://openhome.alipay.com/platform/keyManage.htm?keyType=partner
$alipay_config['partner']		= '2088721600105567';

//商户的私钥,此处填写原始私钥去头去尾，RSA公私钥生成：https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.nBDxfy&treeId=58&articleId=103242&docType=1
$alipay_config['private_key']	= 'IICXAIBAAKBgQDbrazS0Hf7iNFK6dhVCA6dVX087xeAulEndBzZ84Ib1uF+hX+P
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
AeDI1w3S32XFcT3VyZ6g8qlpfKkf2mx/dK26k7QRaUg=';
/*$alipay_config['private_key']	= '-----BEGIN RSA PRIVATE KEY-----
MIICXAIBAAKBgQDUGIEvxyq+StVshdWFmetA+H3862v2I+4s5dEHkknm7e9sUKYC
vMZSm6Nh89m//fjRgkIPo2ixvU8DaQPn1kTCzfauLdimf7Uh+UctO5nwUDdpw/Hu
x3Xm6JMwWrtWA0YquEbO3IaNGoTV8dpERLQncGYZnLRlwHlXITkstI1pMwIDAQAB
AoGAQXDSI2jDcnVqhpKAwDkukhxZ2zjUVdzeNzItnbVwYfVWI0h7AGs4kfJ9pdJ0
hD2bkSEsuuCDhBvSDk5Pqy+8hQKLnrIYPNLtpcwIaKkndk3+MA+4cem9zGdBTgFY
MDD5VNcaJg27jjfcZ7n5TfZTtGSIiGYHd5s23/vmQRVt3eECQQD7eQCQR4rcjaJS
5DUbsfbB8cwXYMs3PjxjnDI5nGi26dfGyQ9zPvfgQY9MqRAEfKOvD1U2RWqzes/T
AOYaXPsjAkEA1+oFM6P8NnyT+TTW9e+0T9a2c3njDsi/wyrokttT7oRu/h0MPzcM
qGKaLzzTryldPXwgxhhQbz5FCGI4DBSCsQJBAPstXXa/PuAVSDFrZ/CFzWbi0Wv4
boJ7U25bMX+BzsYExFX1tczy9Du2wB9eLnWM2SGeOwq+Q1mKLdMgbrWecekCQAlH
BPIzGaM9tx3+Jz5qDlVf5HcRxa/c8GByd4vX4MNe7WX92Yjd1K1njzh4ZKAiJt99
desNIGenRVAW6FGckAECQFURI1JlrHictadZhQaERTJ8gtWzMMITjzSYI7CwJmFR
PTbDd0bQmuHrpHLIb4t2y8ObQT8kkqU/NcjIP85QMFs=
-----END RSA PRIVATE KEY-----';*/

//支付宝的公钥，查看地址：https://openhome.alipay.com/platform/keyManage.htm?keyType=partner
$alipay_config['alipay_public_key']= 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCnxj/9qwVfgoUh/y2W89L6BkRAFljhNhgPdyPuBV64bfQNN1PjbCzkIM6qRdKBoLPXmKKMiFYnkd6rAoprih3/PrQEB/VsW8OoM8fxn67UDYuyBTqA23MML9q1+ilIZwBC2AQ2UBVOrFXfFl75p6/B5KsiNG9zpgmLCUYuLkxpLQIDAQAB';

//异步通知接口
$alipay_config['service']= 'mobile.securitypay.pay';
//↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑

//签名方式 不需修改
$alipay_config['sign_type']    = strtoupper('RSA');

//字符编码格式 目前支持 gbk 或 utf-8
$alipay_config['input_charset']= strtolower('utf-8');

//ca证书路径地址，用于curl中ssl校验
//请保证cacert.pem文件在当前文件夹目录中
$alipay_config['cacert']    = getcwd().'/cacert.pem';

//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
$alipay_config['transport']    = 'http';
?>