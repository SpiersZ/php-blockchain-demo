<?php
namespace console\controllers;
use common\services\EthereumService;
use common\services\WalletService;
use yii\console\Controller;

class TestController extends Controller
{
    public function actionSend(){
        /**
         * dune whip sugar fix cloud cushion track jaguar magic hospital idea mercy
         * e1977223668f697897e5c51e878b3fd7e65f7c3dad28eb80319e10ced77063c1
         * 02c8e67f20aad6ac68a53bc6fb4282f09a8fe050be10ef7e7c73e93f91d0cbc817
         * 0xe845c1b4f72de46155a1c1b5028642146e7e22ab
         *
         * #############
         * festival razor forum man exotic injury glad feed suit person lend either
         * d06f63e091205d2eeb47c03af39937b9df2a7d7eca9f60ddaead80f9fb273524
         * 0391fe33cbb0040196093a0205c697a70ea0328fd6f9c2c1e1ff66c29654b3ee80
         * 0xb9b002991c375d67bef651d92768f8f5e8d48999
         */

        EthereumService::send('dune whip sugar fix cloud cushion track jaguar magic hospital idea mercy', '0xe845c1b4f72de46155a1c1b5028642146e7e22ab', '0xb9b002991c375d67bef651d92768f8f5e8d48999', 1);
    }

    public function actionNewAccount(){
        $wallet = WalletService::genHDWallet();
        var_dump($wallet);
    }
}