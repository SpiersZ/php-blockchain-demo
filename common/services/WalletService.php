<?php
namespace common\services;

use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Key\Factory\HierarchicalKeyFactory;
use BitWasp\Bitcoin\Mnemonic\Bip39\Bip39Mnemonic;
use BitWasp\Bitcoin\Mnemonic\Bip39\Bip39SeedGenerator;
use BitWasp\Bitcoin\Mnemonic\MnemonicFactory;
use Web3\Utils;
use Web3p\EthereumUtil\Util;

class WalletService
{

    /**
     * 生成一个hd钱包，并返回一个erh账户
     * @return array
     * @throws \BitWasp\Bitcoin\Exceptions\RandomBytesFailure
     */
    public static function genHDWallet()
    {
        // echo "mnemonic: " . $mnemonic . PHP_EOL . PHP_EOL;
        $mnemonic = self::genMnemonic();
        $ethAccount = self::getEthAccountByMnemonic($mnemonic);
        $wallet = array_merge([
            "mnemonic" => $mnemonic
        ], $ethAccount);
        return $wallet;
    }

    /**
     * 床架你一个账户，生成助记词并返回
     * @return string
     * @throws \BitWasp\Bitcoin\Exceptions\RandomBytesFailure
     */
    public static function genMnemonic(){
        // Bip32
        $random = new Random();
        $entropy = $random->bytes(Bip39Mnemonic::MIN_ENTROPY_BYTE_LEN);
        $bip39 = MnemonicFactory::bip39();
        // 助记词
        $mnemonic = $bip39->entropyToMnemonic($entropy);
        return $mnemonic;
    }


    /**
     * 通过助记词生成eth账户
     * @param $mnemonic
     * @param int $i
     * @return array
     * @throws \Exception
     */
    public static function getEthAccountByMnemonic($mnemonic, $i = 0) {
        $seedGenerator = new Bip39SeedGenerator();
        $seed = $seedGenerator->getSeed($mnemonic);
//        echo "seed: " . $seed->getHex() . PHP_EOL;
        $hdFactory = new HierarchicalKeyFactory();
        $master = $hdFactory->fromEntropy($seed);
        // 主私钥
        //echo "master private key: " . $master->getPrivateKey()->getHex() . PHP_EOL;
        // 主公钥
        //echo "master public key: " . $master->getPublicKey()->getHex() . PHP_EOL . PHP_EOL;

        $util = new Util();
//        echo "Bip44 ETH account $i " . PHP_EOL;
        $hardened = $master->derivePath("44'/60'/$i'/0/0");
//        echo " - m/44'/60'/$i'/0/0 " . PHP_EOL;
//
//        echo " public key: " . $hardened->getPublicKey()->getHex() . PHP_EOL;
//        echo " private key: " . $hardened->getPrivateKey()->getHex() . PHP_EOL;
//        echo " address: " . $util->publicKeyToAddress($util->privateKeyToPublicKey($hardened->getPrivateKey()->getHex())) . PHP_EOL . PHP_EOL;
        return [
            "privateKey" => $hardened->getPrivateKey()->getHex(),
            "publicKey" => $hardened->getPublicKey()->getHex(),
            "address" => $util->publicKeyToAddress($util->privateKeyToPublicKey($hardened->getPrivateKey()->getHex()))
        ];
    }

    /**
     * 通过通过私钥获取地址
     * @param $privateKey 私钥
     * @return array
     * @throws \Exception
     */
    public static function getEthAccountByPrivateKey($privateKey) {
        $util = new Util();
        return [
            "privateKey" => $privateKey,
            "publicKey" => $util->privateKeyToPublicKey($privateKey),
            "address" => $util->publicKeyToAddress($util->privateKeyToPublicKey($privateKey))
        ];
    }

    /**
     * 通过助记词获取私钥
     * @param $mnemonic
     * @param int $i
     * @return array
     * @throws \Exception
     */
    public static function getEthPrivateKeyByMnemonic($mnemonic, $i = 0) {
        $seedGenerator = new Bip39SeedGenerator();
        $seed = $seedGenerator->getSeed($mnemonic);
        $hdFactory = new HierarchicalKeyFactory();
        $master = $hdFactory->fromEntropy($seed);
        $hardened = $master->derivePath("44'/60'/$i'/0/0");
        return $hardened->getPrivateKey()->getHex();
    }
}