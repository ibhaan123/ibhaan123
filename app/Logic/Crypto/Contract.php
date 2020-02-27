<?php
namespace phpEther\Web3\Api\Eth;
use BitWasp\Buffertools\Buffer;
use phpEther\Encoder\Keccak;
use phpEther\Tools\Hex;

class Contract
{
  

    public function __construct(\phpEther\Web3\Api\Eth $eth, $abi)
    {
		parent:: __construct($eth, $abi);
   
    }
	

	
	public function __call($method, $arguments)
    {
		if (isset($this->abi[self::ABI_TYPE_FUNCTION][$method])) {
			$abi = $this->abi[self::ABI_TYPE_FUNCTION][$method];
			$tx = new\phpEther\Transaction();
			if(count($arguments) > count($abi["inputs"])){
				$rtx = $arguments[count($abi["inputs"])];
				if($rtx instanceof \phpEther\Transaction){
					$tx= $rtx;
				}
				if($rtx instanceof \phpEther\Account ){
					$tx = new\phpEther\Transaction($rtx);
				}
				unset($arguments[count($abi["inputs"])]);
			}
			else
			{
				foreach($arguments as $arg){
					if($arg instanceof \phpEther\Account )
					$tx = new\phpEther\Transaction($arg);
				}
			}
			$payload = $tx->setWeb3($this->eth->web3);
			if(!empty($arguments)){
				if (!isset($this->abi[self::ABI_TYPE_FUNCTION][$method])) {
					throw new \Exception("Method does not exists in abi");
				}
				$abiarray = $this->abi[self::ABI_TYPE_FUNCTION][$method]['inputs'];
				$arguments = $this->getArgumentBuffer($abiarray, $arguments);
			}
			$payload->setTo(Hex::buffer($this->address));
			$payload->setData($this->getMethodBin($method, $arguments));	
			if($abi["constant"]){
				return  $this->decodeMethodResponse($method, $this->eth->call($payload));
			}else {
				return $payload->prefill();
			}
        }elseif (isset($this->abi[self::ABI_TYPE_EVENT][$method])) {
			$payload = $arguments;
			$payload["to"] = $this->address;
			$payload["data"] = $this->contract->getEventBin($method);	
			return $this->eth->call($payload);
        }else{
			throw new \Exception("Method does not exists in abi"); 
		}
        return null; 
    }
	

}