<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/22
 * Time: 10:39
 */

namespace api\helpers;


class Response
{

    /**
     * 全局错误码
     */
    const CODE_SUCCESS = 0;
    const CODE_PARAMS_ERROR = 400;
    const CODE_SERVER_ERROR = 500;

    /**
     * @var array 全部代码配置
     */
    static $code_config = [
        self::CODE_SUCCESS => 'succ',
        self::CODE_PARAMS_ERROR => 'params error',
        self::CODE_SERVER_ERROR => 'server error',
    ];

    public static function getCodeLabel($code){
        return self::$code_config[$code];
    }


    /**
     * @param $code
     * @param array $data
     * @param null $error_type
     * @param null $error_code
     * @return array
     */
    public static function response($code,$data = [], $msg = ''){
        if(empty($msg) && isset(self::$code_config[$code])){
            $msg = self::getCodeLabel($code);
        }
        $response_data = self::buildResponse($code, $data, $msg);
        return $response_data;
    }

    /**
     * @param $code
     * * @param null $data
     * @param string $msg
     * @return array
     */
    public static function buildResponse($code, $data = null, $msg = ''){

        $format = ['code' => $code];

        $format = array_merge($format, [
            'data'  => $data,
            'msg' => $msg,
        ]);

        return $format;
    }
}