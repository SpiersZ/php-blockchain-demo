<?php


namespace common\services;

use GuzzleHttp\Promise\Promise;
use Web3\Contract;
use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManager;
use Web3\Utils;
use Web3\Web3;
use Web3p\EthereumTx\Transaction;
use Web3p\EthereumUtil\Util;

/**
 * 发送(合约)交易三部曲
 * 1. 构造发送交易的data
 * 2. 对data进行签名
 * 3. 使用web3的sendRawTransaction广播交易
 */

class EthereumService
{

    public static function getWeb3(){
        $rpc = \Yii::$app->params['eth_rpc'];
        $web3 = new Web3(new HttpProvider(new HttpRequestManager($rpc, 30)));
        return $web3;
    }

    /**
     * 获取事件对象
     * @param $address
     * @param $abi
     * @return Contract
     */
    public static function getContract($address, $abi){
        $contract = new Contract(\Yii::$app->params['eth_rpc'], $abi);
        return $contract->at($address);
    }

    /**
     * 获取余额
     * @param $address
     * @return int
     */
    public static function getBalance($address){
        $web3 = self::getWeb3();
        $balance = 1;
        $web3->eth->getBalance($address, function($err, $data) use ($web3, &$balance){
            if ($err !== null) {
                \Yii::error(print_r($err->getMessage(), true));
                throw new \Exception($err->getMessage());
            }
            $balance = self::fromWei($data->toString());
        });
        return $balance;
    }

    /**
     * 单位换算
     * @param $wei
     * @return false|float|int
     */
    public static function fromWei($wei){
        if($wei == 0){
            return 0;
        }
        list($quotient, $residue) = Utils::fromWei($wei,'ether');
        if($residue->toString() == 0){
            return $quotient->toString();
        }
        $result =  round(floatval($quotient->toString().'.'.$residue->toString()), 6);
        return $result;
    }

    /**
     * 判断是否是地址
     * @param $address
     * @return bool
     */
    public static function isAddress($address){
        return Utils::isAddress($address);
    }

    public static function getTokenBalance(){

    }

    /**
     * 发送eth
     * @param $mnemonic 助记词
     * @param $from 发送方地址
     * @param $to 目标地址
     * @param $amount 数量
     * @return string
     * @throws \Exception
     */
    public static function send($mnemonic, $from, $to , $amount){
        $nonce = self::getNonce($from);
        $transaction = self::createTransaction($nonce, $from, $to, Utils::toWei($amount, 'ether'), '');
        $private_key = WalletService::getEthPrivateKeyByMnemonic($mnemonic);
        $transaction->sign($private_key);
        $raw = '0x' . $transaction->serialize()->toString('hex');
        
        $transaction_hash = self::sendRawTransaction($raw);

        return $transaction_hash;
    }

    /**
     * 发送token
     * @param $mnemonic 助记词
     * @param $contract 合约地址
     * @param $from 发送方地址
     * @param $to 目标地址
     * @param $amount 数量
     * @return string
     * @throws \Exception
     */
    public static function sendToken($mnemonic, $contract, $from, $to , $amount){
        // 助记词转私钥
        $private_key = WalletService::getEthPrivateKeyByMnemonic($mnemonic);

        // 通过私钥转账
        $transaction_hash = self::sendTokenByPrivateKey($private_key, $contract, $from, $to , $amount);
        return $transaction_hash;
    }


    /**
     * 发送token
     * @param $private_key 助记词
     * @param $contract 合约地址
     * @param $from 发送方地址
     * @param $to 目标地址
     * @param $amount 数量
     * @return string
     * @throws \Exception
     */
    public static function sendTokenByPrivateKey($private_key, $contract, $from, $to , $amount){
        // 通过abi生成合约对象
        $contract_obj = self::getContract($contract, '[{"constant":true,"inputs":[],"name":"name","outputs":[{"internalType":"string","name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"internalType":"address","name":"_spender","type":"address"},{"internalType":"uint256","name":"_value","type":"uint256"}],"name":"approve","outputs":[{"internalType":"bool","name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"totalSupply","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"internalType":"address","name":"_from","type":"address"},{"internalType":"address","name":"_to","type":"address"},{"internalType":"uint256","name":"_value","type":"uint256"}],"name":"transferFrom","outputs":[{"internalType":"bool","name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"INITIAL_SUPPLY","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"decimals","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[{"internalType":"address","name":"_owner","type":"address"}],"name":"balanceOf","outputs":[{"internalType":"uint256","name":"bal","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"symbol","outputs":[{"internalType":"string","name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"internalType":"address","name":"_to","type":"address"},{"internalType":"uint256","name":"_value","type":"uint256"}],"name":"transfer","outputs":[{"internalType":"bool","name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"internalType":"address","name":"_owner","type":"address"},{"internalType":"address","name":"_spender","type":"address"}],"name":"allowance","outputs":[{"internalType":"uint256","name":"remaining","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"inputs":[],"payable":false,"stateMutability":"nonpayable","type":"constructor"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"owner","type":"address"},{"indexed":true,"internalType":"address","name":"spender","type":"address"},{"indexed":false,"internalType":"uint256","name":"value","type":"uint256"}],"name":"Approval","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"from","type":"address"},{"indexed":true,"internalType":"address","name":"to","type":"address"},{"indexed":false,"internalType":"uint256","name":"value","type":"uint256"}],"name":"Transfer","type":"event"}]
');
        $data = $contract_obj->getData('transfer', $to, Utils::toWei($amount, 'ether'));
        // 获取nonce
        $nonce = self::getNonce($from);
        // 创建交易
        $transaction = self::createTransaction($nonce, $from, $contract, 0, $data);
        // 通过私钥对交易进行签名
        $transaction->sign($private_key);
        $raw = '0x' . $transaction->serialize()->toString('hex');
        // 广播交易
        $transaction_hash = self::sendRawTransaction($raw);
        return $transaction_hash;
    }

    /**
     * 广播交易
     * @param $raw
     * @return string
     */
    public static function sendRawTransaction($raw) {
        $web3 = self::getWeb3();
        $transaction_hash = '';
        $web3->eth->sendRawTransaction($raw, function($err, $data) use (&$transaction_hash){
            if ($err !== null) {
                \Yii::error(print_r($err->getMessage(), true));
                throw new \Exception($err->getMessage());
            }
            $transaction_hash = $data;
        });
        return $transaction_hash;
    }

    /**
     * 创建交易对象
     * @param $nonce
     * @param $from
     * @param $to
     * @param $value
     * @param $data
     * @return Transaction
     */
    public static function createTransaction($nonce, $from, $to, $value, $data){
        return new Transaction([
            'nonce' => Utils::toHex($nonce,  true),
            'from' => $from,
            'to' => $to,
            'gas' => Utils::toHex(90000, true),
            'gasPrice' => Utils::toHex(Utils::toWei(10, 'gwei'), true),
            'value' => Utils::toHex($value, true),
            'data' => '0x' . $data,
            'chainId' => \Yii::$app->params['chain_id'],
        ]);
    }

    /**
     * 获取最新的nounce值
     * @param $address
     * @return int
     */
    public static function getNonce($address) {
        $web3 = self::getWeb3();
        $nonce = 0;
        $web3->eth->getTransactionCount($address, function($err, $data) use (&$nonce){
            if ($err !== null) {
                \Yii::error(print_r($err->getMessage(), true));
                throw new \Exception($err->getMessage());
            }
            $nonce = $data;
        });
        return $nonce;
    }

    /**
     * 通过交易hash查询交易
     * @param $hash
     * @return null
     */
    public static function getTransaction($hash){
        $web3 = self::getWeb3();
        $transaction = null;
        $web3->eth->getTransactionReceipt($hash, function($err, $data) use (&$transaction){
            if ($err !== null) {
                \Yii::error(print_r($err->getMessage(), true));
                throw new \Exception($err->getMessage());
            }
            $transaction = $data;
        });
        return $transaction;
    }

    /**
     * 获取Token转账事件
     * @param $contract 合约地址
     * @param $fromBlock 具体的块
     * @return mixed
     */
    function getTokenTransferEvent($contract, $fromBlock)
    {
        $web3 = self::getWeb3();
        // topics 指定了合约的转账方法
        $web3->eth->getLogs([
            'topics' => array(Utils::sha3('0xddf252ad1be2c89b69c2b068fc378daa952ba7f163c4a11628f55a4df523b3ef')),
            'address' => $contract,
            'fromBlock' => $fromBlock,
        ],
        function ($err, $result) use (&$output) {
            if ($err !== null) {
                echo $err->getMessage();
            }
            $output = $result;
        });
        $eventInputArray = [
            [
                "internalType"=> "address",
                "name"=> "_from",
                "type"=> "address"
            ],
            [
                "internalType"=> "address",
				"name"=> "_to",
				"type"=> "address"
			],
            [
                "internalType"=> "uint256",
				"name"=> "_value",
				"type"=>  "uint256"
			]
		];
        // 解析事件数据
        return $contract->ethabi->decodeParameters($eventInputArray, $output[0]->data);
    }
}