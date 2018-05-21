<?php
/**
* 配置文件
*/


// 商户号
$merchantNumber = 'API0100005402000638';

// 商户密钥
$mch_key = '111111';

// 支付网关
$gateway_url = 'http://39.108.134.13:10015/api/unified/payment';


//商户私钥 rsa_private_key.pem
$my_private_key = '-----BEGIN PRIVATE KEY-----
MIICXAIBAAKBgQDZmlr7kbCQj1jXEk6L95LWJyk2iyJIzP346Y4b+ZJNML2g1vth
KFQFMs4aOdRkrbWB8pL4iP9qMvWLSE177ocTyuI+ym9n+twhykNUXlDO/v8lVxkf
vrvKefnnpbykj76vDCPXsZVA9y2MyAEhB2Gz+noxRXdMk5HhIBNPva5CQwIDAQAB
AoGAL9y6rGDUNEfDY7L8Id83pfEBKKUbymWEa0572n1fh6jz3L/MlQc4y9fr62rS
474tkUKYvx/rBiHlTlh96oeBoBn1tpEXQbibHjk41b71ocQBFf5dA1r39tlrB3BW
sHaVt5aU7HahdgzeOWJlYdsxhFV/42qndATUuA2rABHRhWkCQQD1J6hhEOID9b0e
E0IlxFhxpSgs/SfQfxGX9ipXlxp4iLhjG1OyB9Ca27uYSBsvkHSQdIOLcP51GQ32
JrJNuWzFAkEA4zqtBcvMqqT9JdHuB/hoq72DD7ySFNuirU6mkR1WkO3/9fmh7jiF
C1mk/YV7HXZvZkDWFXgu9taB0cQp/MtzZwJBANWH7F5XcTWXxli614PDyHF8NEkz
W4l8FFvzGyBdyb2xAEG6nKTVr7oip/xU2fEM9qqYAbfj/I8L+qArRmTynC0CQAay
2B7vnxz4uC3BPo8ll5BYieCQ4o2S2/zX8LkecErdeUo7DbcEG4f2IJMIuFRqBWay
Sp1faaMFN9aDPemh5ckCQEHB0pdnd7EsPcz1VXhBd1uvYDQ67xES6jKl3Q7bix4s
MXAGbJ07FYnFtny5h2g06MCVJQ4Pfsc7W0hc8Fn4OCI=
-----END PRIVATE KEY-----';


// 速汇宝公钥
$shbapi_public_key = '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCrn57jG1EC2vq4m2HZToZEx7ZI
aPp2XfkGVXXMNpk5gJxsbgKa2Q429pidmyj5oA0JIEzYWojwKXF8c9cl1J95y15d
LT9/CJVbpDzAciuKJYLet2fQufrJMCKdY7sJdympwADEPmbOfsnw61WCRZnabWU3
Ke1O8JM2Gd+2OW4guwIDAQAB
-----END PUBLIC KEY-----';





/*
  提示，私钥和公钥需保留文本中的-----BEGIN PUBLIC KEY----- *** -----END PUBLIC KEY-----

*/