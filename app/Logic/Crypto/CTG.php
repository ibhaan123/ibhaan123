<?php
namespace App\Logic\Crypto; 
use App\Logic\Crypto\Multichain; 
use ofumbi\Api\ApiInterface;
class CTG extends Multichain implements ApiInterface
{
	public $addressByte = '4c';
	public $p2shByte='26';
	public $privByte ='35'; 
	public $hdPubByte ='0488b21e';
	public $hdPrivByte = '0488ade4' ;
	public $p2pMagic = 'cffb9ab1';
	public $SegwitBech32Prefix = NULL;
	public $signedMessagePrefix ='ctg_message';
	public $net;
	// RPC INFO //
	public $node;
	public $bip44index = '800';
    public function __construct()
    {
		$url = config('nodes.ctg.url');
		$username = config('nodes.ctg.username');
		$password = config('nodes.ctg.password');
		parent::__construct($url,$username,$password);
	}
	
}

