<?php
namespace leegoway\uic;

class PassCookie {
    /**
     * 字符串加密、解密函数
     *
     *
     * @param   string  $txt        字符串
     * @param   string  $operation  ENCODE为加密，DECODE为解密，可选参数，默认为ENCODE，
     * @param   string  $key        密钥：数字、字母、下划线
     * @return  string
     */
    public static function passC ($txt, $operation = 'ENCODE', $key = '') {
        $key = $key ? $key : "uicAutops1988";
        $txt = $operation == 'ENCODE' ? ( string ) $txt : base64_decode($txt);
        $len = strlen($key);
        $code = '';
        for ($i = 0; $i < strlen($txt); $i ++) {
            $k = $i % $len;
            $code .= $txt [$i] ^ $key [$k];
        }
        $code = $operation == 'DECODE' ? $code : base64_encode($code); return $code;
    }


}