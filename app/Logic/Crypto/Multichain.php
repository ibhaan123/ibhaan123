<?php
namespace App\Logic\Crypto; 
use ofumbi\Api\Providers\Rpc;
use Graze\GuzzleHttp\JsonRpc\Client;
use ofumbi\Api\ApiInterface;
use Illuminate\Support\Collection;
use BitWasp\Bitcoin\Transaction\SignatureHash\SigHash;
class Multichain implements ApiInterface
{
	public $addressByte = '';
	public $p2shByte='';
	public $privByte =''; 
	public $hdPubByte ='';
	public $hdPrivByte = '' ;
	public $p2pMagic = '';
	public $SegwitBech32Prefix = NULL;
	public $signedMessagePrefix ='';
	public $net;
	// RPC INFO
	public $node;
	public $bip44index = '';
	public $url  ;
	public $username ;
	public $password  ;
	
    public function __construct($url, $username , $password  )
    {
		$this->net = $this->network();
		$this->node = new Rpc( $url, $username , $password ); 	
	}
	
	public function getNetwork(){
		return $this->net;
	}

    /**
     * @return NetworkInterface
     * @throws \Exception
     */
    public function network()
    {
		return new Networks\Multichain($this->addressByte, $this->p2shByte, $this->privByte, $this->hdPubByte, $this->hdPrivByte, $this->p2pMagic, $this->SegwitBech32Prefix, $this->signedMessagePrefix);
      
    }
	public function sigHash(){
		return SigHash::ALL;
	}
	//chainso
	public function addressTx(Collection $addresses, $blocks = []){
		return $this->node->addressTx($addresses, $blocks);
	}
	
	// dash
	public function listunspent($minconf, array $addresses=[], $max = null){
		return $this->node->listunspent($minconf, $addresses, $max);
	}
	
	//trezor
	public function getBalance($minConf, array $addresses=[]){
		return $this->node->getBalance($minConf, $addresses );
	}
	
	
	
	public function sendrawtransaction( $hexRawTx ){
		return $this->node->sendrawtransaction( $hexRawTx );
	}
	
	public function getBlock($hash){
	
		return $this->node->getBlock($hash);
	}
	
	public function getBlockByNumber($number){
		return $this->node->getBlockByNumber($number);
	}
	
	public function getTx($hash){
		return $this->node->getTx($hash);
	}
	
	public function currentBlock(){
		return $this->node->currentBlock();
	}
	
	public function feePerKB(){
		return $this->node->feePerKB();
	}
	//
	public function importaddress($address,$wallet_name =null,$rescan =null){
		return $this->node->importaddress($address,$wallet_name,$rescan);
	}
	
	
}

