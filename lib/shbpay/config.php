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
$str = 'MIICXgIBAAKBgQDIIqFP0DAKfDjcTrK4dXlu2faZxwwepTZJiQ/nUe4HibKkkDM2ISX1GCq3yqKcdZ/i00+oBJKnU8JS/H9b7VffYfW1cvdT6ewJi0zs58uSHEl0MfnguCqa+c/kxTz9SZxjoJtJn2mFTRTWPoMUcMEbBe/OGpO0qxw7NrvlEPjznwIDAQABAoGBALv3SmobrgMrCHxhrZlxO8vRCrsDJ27g3EvUXwty94w813uXs4FzQKMhP32+41rvXms66+Vx5gshr7EhVLyR/aQ11GZArzfJ4YVh78L1HMvSRQdeSZttX3r6ha0OhUIqb0olds7Rfn/HUCKkF1pIqJJSouWNiPrTdrqEyG6Hhl2BAkEA8bHdIg+B5LpHNSuEMJzb/pHYkvJQO1C4KFpL1ZLHTrfoyNlRoJdkgacSFjms92p/ZHYFt5v/k4aU/aaGYoVUgwJBANP7D4cM++fxNpOp34uYpdD8pFMp7Y49mk8HGcAkUZj/i3WZbL97FNvdp3VwEuUQOUvxdrvPz9uv8XAargDykbUCQBAOowRaR2Tsw0JTC8s3AJ6fDeZlVtM5/6IerZ8Yy0j9iuedGIE0zaaXdOopFsaxOplwBd027fndHRTk9VSiSwUCQQDLqIV+hqbua/y3WZmAEKQeaNunWrz0z5KVIolG5J3QlJNz5Hdqb+HWatocJhTBJhkmMVKVlk5pDly0C63pEyORAkEAhrcWVtOAuTXIfj1VGa8J9mNuoDG15jsTzs+P0oscimbLQ0JNb8OZ+NMGtSbM8kPw69J/Yhzuew/rJBS118MG0g==';
$str = chunk_split($str, 64, "\n");
$my_private_key = "-----BEGIN RSA PRIVATE KEY-----\n$str-----END RSA PRIVATE KEY-----\n";

// 速汇宝公钥
$str = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCrn57jG1EC2vq4m2HZToZEx7ZIaPp2XfkGVXXMNpk5gJxsbgKa2Q429pidmyj5oA0JIEzYWojwKXF8c9cl1J95y15dLT9/CJVbpDzAciuKJYLet2fQufrJMCKdY7sJdympwADEPmbOfsnw61WCRZnabWU3Ke1O8JM2Gd+2OW4guwIDAQAB';
$str = chunk_split($str, 64, "\n");
$shbapi_public_key = "-----BEGIN PUBLIC KEY-----\n$str-----END PUBLIC KEY-----\n";


/*
  提示，私钥和公钥需保留文本中的-----BEGIN PUBLIC KEY----- *** -----END PUBLIC KEY-----

*/