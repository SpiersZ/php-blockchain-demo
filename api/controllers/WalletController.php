<?php


namespace api\controllers;

use api\helpers\Response;
use common\services\EthereumService;
use common\services\EtherscanService;
use common\services\WalletService;

class WalletController extends BaseController
{

	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		$behaviors = parent::behaviors();
		return $behaviors;
	}

    /**
     * 生成新的钱包地址
     * @return array
     */
    public function actionGenerate(){
	    $wallet = WalletService::genHDWallet();
	    return Response::response(Response::CODE_SUCCESS, $wallet);
    }

    /**
     * 查询用户eth地址或token地址
     * @param address 用户地址
     * @param contract 合约地址 查询eth余额为空
     * @return array
     */
    public function actionBalance() {
	    $address = \Yii::$app->request->get('address');
	    if(!EthereumService::isAddress($address)){
            return Response::response(Response::CODE_PARAMS_ERROR);
        }
	    $contract = \Yii::$app->request->get('contract');
	    if(empty($contract)){
            $balance = EtherscanService::getBalance($address);
        }else{
	        if(!EthereumService::isAddress($contract)){
                return Response::response(Response::CODE_PARAMS_ERROR);
            }
            $balance = EtherscanService::getTokenBalance($address, $contract);
        }

	    if($balance === false){
            return Response::response(Response::CODE_SERVER_ERROR);
        }

	    return Response::response(Response::CODE_SUCCESS, [
            'balance' => (string)$balance
        ]);
    }

    /**
     * @param address 用户地址
     * @return array
     */
    public function actionTransactionList() {
        $page = \Yii::$app->request->get('page');
        $page = empty($page) ? 1 : $page;

        $pageSize = \Yii::$app->request->get('pageSize');
        $pageSize = empty($pageSize) ? 10 : $pageSize;

        $address = \Yii::$app->request->get('address');

        if(!EthereumService::isAddress($address)){
            return Response::response(Response::CODE_PARAMS_ERROR);
        }
        $sort = 'desc';
        $list = [];

        $contract = \Yii::$app->request->get('contract');
        if(empty($contract)){
            $list = EtherscanService::getTransactionList($address, $page, $pageSize, $sort);
        }else{
            if(!EthereumService::isAddress($contract)){
                return Response::response(Response::CODE_PARAMS_ERROR);
            }
            $list = EtherscanService::getTokenTransactionList($address, $contract, $page, $pageSize, $sort);
        }

        return Response::response(Response::CODE_SUCCESS, [
            'list' => $list,
            'page' => $page,
            'pageSize' => $pageSize
        ]);
    }

    /**
     * 发送Token
     * @return array
     */
    public function actionSendToken() {
        $mnemonic = \Yii::$app->request->post('mnemonic');
        $from = \Yii::$app->request->post('from');
        $to = \Yii::$app->request->post('to');
        $contract = \Yii::$app->request->post('contract');
        $amount = \Yii::$app->request->post('amount');
        if(empty($mnemonic) || empty($from) || empty($to) || empty($contract)){
            return Response::response(Response::CODE_PARAMS_ERROR);
        }

        if(empty($amount) || $amount < 0){
            $amount = 0;
        }

        try {
            $hash = EthereumService::sendToken($mnemonic, $contract, $from, $to, $amount);
        }catch (\Exception $e){
            return Response::response(Response::CODE_SERVER_ERROR, [], $e->getMessage());
        }

        return Response::response(Response::CODE_SUCCESS, [
            'hash' => $hash,
        ]);
    }

    /**
     * 获取交易详情
     * @return array
     */
    public function actionGetTransaction(){
        $hash = \Yii::$app->request->get('hash');
        if(empty($hash)){
            return Response::response(Response::CODE_PARAMS_ERROR);
        }
        try{
            $transaction = EthereumService::getTransaction($hash);
        }catch (\Exception $e){
            return Response::response(Response::CODE_SERVER_ERROR, [], $e->getMessage());
        }
        return Response::response(Response::CODE_SUCCESS, $transaction);
    }
}