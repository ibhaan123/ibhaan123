<?php
namespace App\Logic\Crypto;
use ofumbi\Api\Providers\Insight;
class BTCTESTNET extends \ofumbi\Api\BTCTESTNET implements \ofumbi\Api\ApiInterface
{
	public  $blockexplorer ,  // api providers
			 $bitpay , 
			 $bitcoin ,  
			 $trezor1, 
			 $trezor2, 
			 $trezor3,
			 $chainso, 
			 $coinspace, 
			 $net;
	public function __construct() // well use varoius api to handle rate limmiting
    {
		$this->net = $this->network();
		$this->blockexplorer = new Insight('https://testnet.blockexplorer.com/api/'); //
		$this->bitpay = new Insight('https://testnet.blockexplorer.com/api/');  // getTx
	}
	
	 /**
     * @return NetworkInterface
     * @throws \Exception
     */
    public function network()
    {
         return \BitWasp\Bitcoin\Network\NetworkFactory::bitcoinTestnet();
    }

}