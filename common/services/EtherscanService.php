<?php


namespace common\services;


use EtherScan\EtherScan;
use EtherScan\Resources\ApiConnector;

class EtherscanService
{
    public static $etherScan;

    public static function getEtherScan(){

        if(self::$etherScan instanceof EtherScan){
            return self::$etherScan;
        }
        $esApiConnector = new ApiConnector(\Yii::$app->params['api_key']);
        self::$etherScan = new EtherScan($esApiConnector);
        return self::$etherScan;
    }

    /**
     * 获取余额
     * @param string $address
     * @return float|int
     */
    public static function getBalance(string $address){
        $etherScan = self::getEtherScan();
        $result = json_decode($etherScan->getAccount(\Yii::$app->params['network'])->getBalance($address),true);
        if(intval($result['status']) == 1){
            return EthereumService::fromWei($result['result']);
        }
        return 0;
    }

    /**
     * 获取Token余额
     * @param array $address
     * @param array $contractaddress
     * @return bool|float
     */
    public static function getTokenBalance(string $address, string $contractaddress)
    {
        $etherScan = self::getEtherScan();
        $result = json_decode($etherScan->getAccount(\Yii::$app->params['network'])->getTokenBalance($address, $contractaddress),true);
        if(intval($result['status']) == 1){
            return EthereumService::fromWei($result['result']);
        }
        return 0;
    }

    /**
     * 获取eth交易列表
     * @param string $address
     * @param int $page
     * @param int $pageSize
     * @param string $sort
     * @return array
     */
    public static function getTransactionList(string $address,int $page,int $pageSize,string $sort){
        $etherScan = self::getEtherScan();

        $result = json_decode($etherScan->getAccount(\Yii::$app->params['network'])->getTransactions($address, $page, $pageSize, $sort),true);
        $list = [];
        if(intval($result['status']) == 1){
            for($i = 0; $i < count($result['result']); $i++){
                $list[] = [
                  'hash' => $result['result'][$i]['hash'],
                  'timeStamp' => $result['result'][$i]['timeStamp'],
                  'from' => $result['result'][$i]['from'],
                  'to' => $result['result'][$i]['to'],
                  'isError' => $result['result'][$i]['isError'],
                  'value' => (string)EthereumService::fromWei($result['result'][$i]['value']),
                ];
            }
        }
        return $list;
    }

    /**
     * 获取token 交易列表
     * @param string $address
     * @param string $contractaddress
     * @param int $page
     * @param int $pageSize
     * @param string $sort
     * @return array
     */
    public static function getTokenTransactionList(string $address,string $contractaddress,int $page,int $pageSize,string $sort){
        $etherScan = self::getEtherScan();
        $result = json_decode($etherScan->getAccount(\Yii::$app->params['network'])->getTokenTransactions($address, $contractaddress, $page, $pageSize, $sort),true);
        $list = [];
        if(intval($result['status']) == 1){
            for($i = 0; $i < count($result['result']); $i++){
                $list[] = [
                    'hash' => $result['result'][$i]['hash'],
                    'timeStamp' => $result['result'][$i]['timeStamp'],
                    'from' => $result['result'][$i]['from'],
                    'to' => $result['result'][$i]['to'],
                    'isError' => 0,
                    'value' => (string)EthereumService::fromWei($result['result'][$i]['value']),
                ];
            }
        }
        return $list;
    }
}