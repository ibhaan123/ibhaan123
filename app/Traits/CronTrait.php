<?php
namespace App\Traits;
use App\Models\Country;
use App\Models\Token;
use \App\Models\Ad;
use App\Models\Minute;
use App\Models\Day;
use App\Models\Week;
use App\Models\Month;
use App\Models\Market;
use \Illuminate\Support\Facades\DB;

Trait  CronTrait {
	
  public function request($url, $req, $type = 'GET'){
		$client = new \GuzzleHttp\Client();
		$request = $type =='GET'?'query':'form_params';
		try {
			$response = $client->request($type, $url, [
				$request => $req
			]);
		} catch (\GuzzleHttp\Exception\TransferException $e) {
			$err =" Client Error > ". \GuzzleHttp\Psr7\str($e->getRequest());
			if ($e->hasResponse()) {
				$err.=" ". \GuzzleHttp\Psr7\str($e->getResponse());
			}
			throw new \Exception ($err);
		}
		$json = json_decode($response->getBody());
		//echo $response->getBody();
        if (isset($json->error)) {
            throw new \Exception("Request API error: {$json}");
        }
        return $json;
	}
	
public function openExchange() {
		$api = 'https://openexchangerates.org/api/latest.json';
		$key = setting('openExchangeRatesApiKey', false);
		if ($key) {
			$json = $this->request($api,['app_id'=>$key]);
			if (isset($json->rates)) {
				foreach ($json->rates as $symbol => $rate) {
					$currency = \App\Models\Country::currency($symbol)->first();
					if(!$currency)continue;
					$currency->exchange_rate = $rate;
					$currency->save();
				}
			}
		}
	}
	
	
	public function cryptoCompareRates($new=false) {
		$api ='https://min-api.cryptocompare.com/data/pricemulti';
		if($new)
			$rates = Ad::whereNull('rate')->get();
		else
			$rates = Ad::whereNotNull('rate')->get();
		$merged = [];
		foreach ($rates->chunk(80) as $rate_chunk ) {
			/*$json = $this->request($api,[
				'fsyms'=>$rate_chunk->unique('from_symbol')->implode('from_symbol', ','),
			]);*/
			$url = $api.'?fsyms='.$rate_chunk->unique('from_symbol')->implode('from_symbol', ',').'&tsyms='.$rate_chunk->unique('to_symbol')->implode('to_symbol', ',')  ;
			$json = json_decode(file_get_contents($url),true);
			/**/
			
			foreach ($json as $key => & $value)
			{
				$key = trim($key);
				if (is_array($value) && isset($merged[$key]) && is_array($merged[$key]))
				{
					$merged[$key] = array_merge($merged[$key], $value);
				} else
					$merged[$key] = $value;
			}
		}
		
		$json = json_decode(json_encode($merged));
		$updates = [];
		foreach ($rates as $rate ) {
			if(isset($json->{$rate->from_symbol}->{$rate->to_symbol})){
				$update = [];
				if(is_null($rate->rate)){
					$update['active']  = 1;
					if(config('ads.auto_approve')){
						$update['status']  = 'approved';
					}
				}
				$mktrate = $json->{$rate->from_symbol}->{$rate->to_symbol};
				$overhead = bcmul($mktrate, bcdiv( $rate->overhead, 100 , 2 ),2);
				if($rate->type =='sell'){
					$update['rate']  = bcadd($mktrate, $overhead, 2);
				}elseif($rate->type =='buy'){
					$update['rate'] = bcsub($mktrate, $overhead, 2);
				}
				$update['market_rate'] = $mktrate;
				$update['id'] = $rate->id;
				$updates[] = $update;
			}

		} 
		$table = Ad::getModel()->getTable();
		\Batch::update($table, $updates, 'id');
	}
	
	
 
	public function cryptoCompare() {
		$api ='https://min-api.cryptocompare.com/data/pricemultifull';
		$tokens = \App\Models\Token::all();
		$siteCurrency = setting('siteCurrency','USD');
		foreach ($tokens->chunk(60) as $token_chunk ) {
			$json = $this->request($api,[
				'fsyms'=>$token_chunk->implode('symbol', ','),
				'tsyms'=>$siteCurrency 
			]);
			
			if (!isset($json->Response) || $json->Response != 'Error') {
				$tokens =[];
				foreach ($json->RAW as $symbol => $crypto) {
					$update = \App\Models\Token::symbol($symbol)->first();
					if(!$update)continue;
					$token['id'] = $update->id;
					$token['price'] = $crypto->$siteCurrency->PRICE;
					$token['change']  = $crypto->$siteCurrency->CHANGE24HOUR;
					$token['change_pct'] = $crypto->$siteCurrency->CHANGEPCT24HOUR;
					$token['open'] = $crypto->$siteCurrency->OPEN24HOUR;
					$token['low'] = $crypto->$siteCurrency->LOW24HOUR;
					$token['high'] = $crypto->$siteCurrency->HIGH24HOUR;
					$token['supply'] = $crypto->$siteCurrency->SUPPLY;
					$token['market_cap'] = $crypto->$siteCurrency->MKTCAP;
					$token['volume'] = $crypto->$siteCurrency->VOLUME24HOUR;
					$token['volume_ccy'] = $crypto->$siteCurrency->VOLUME24HOURTO;
					$tokens[] = $token;
				}
				$table = Token::getModel()->getTable();
				\Batch::update($table, $tokens, 'id');
			} 
		}
	}
	/**
     * create data table for minute data.
     * /ajax/history?symbol=<ticker_name>&from=<unix_timestamp>&to=<unix_timestamp>&resolution=<resolution>
     * @return \Illuminate\Http\Response
     */
    public function history($resolution="1")
    {
		$markets = Market::all();
		foreach( $markets as $mkt):
			collect($this->ohdata($mkt->pair, $resolution))->map(function($item,$key)use($mkt, $resolution){
				if($resolution == "M1")
					$ret = new Month();
				elseif($resolution == "D1")
					$ret = new Day();	
				elseif($resolution == "W1")
					$ret = new Week();
				else
					$ret = new Minute();
				$ret->time = \Carbon\Carbon::createFromTimestamp($item->buckettime);
				$ret->volume = floatval(number_format($item->volume,$mkt->base->decimals < 8?$mkt->base->decimals:8,'.',''));
				$ret->open = floatval(number_format($item->open,$mkt->quote->decimals < 8?$mkt->quote->decimals:8,'.',''));
				$ret->high = floatval(number_format($item->high,$mkt->quote->decimals < 8?$mkt->quote->decimals:8,'.',''));
				$ret->low =	floatval(number_format($item->low,$mkt->quote->decimals < 8?$mkt->quote->decimals:8,'.',''));
				$ret->close = floatval(number_format($item->close,$mkt->quote->decimals < 8?$mkt->quote->decimals:8,'.',''));
				$ret->pair=$mkt->pair;
				$ret->market_id = $mkt->id;
				$ret->save();
				return $ret;
			});
		endforeach;
	}
	
	protected function ohdata($pair,$resolution ){
		$last = Minute::latest()->where('pair',$pair)->first();
		$start = $last?$last->time->timestamp:now()->subHour()->startOfHour()->timestamp;
		if(preg_match('([a-zA-Z]+(?: [a-zA-Z]+)*)', $resolution)){
			$period = str_split($resolution);
			switch($period[1]){
				case 'D':
					$timeslice = 86400 * $period[0];
					$last = Day::latest()->where('pair',$pair)->first();
					$start = $last?$last->time->timestamp:now()->subDay()->startOfDay()->timestamp;
					break;
				case 'W':
					$timeslice = 604800 * $period[0];
					$last = Week::latest()->where('pair',$pair)->first();
					$start = $last?$last->time->timestamp:now()->subWeek()->startOfWeek()->timestamp;
					break;
				case 'M':
					$timeslice = 2592000 * $period[0];
					$last = Month::latest()->where('pair',$pair)->first();
					$start = $last?$last->time->timestamp:now()->subMonth()->startOfMonth()->timestamp;
					break;
				default:
					$timeslice = 60;
					$last = Minute::latest()->where('pair',$pair)->first();
					$start = $last?$last->time->timestamp:now()->subHour()->startOfHour()->timestamp;
					break;
			}
		}else{
			$timeslice = $resolution * 60;
		}
		
		return DB::select(DB::raw("
		  SELECT 
			market_id,
			ROUND((CEILING(UNIX_TIMESTAMP(`created_at`) / $timeslice) * $timeslice)) AS buckettime,
			SUBSTRING_INDEX(GROUP_CONCAT(CAST(price AS CHAR) ORDER BY created_at), ',', 1 ) AS `open`,
			SUBSTRING_INDEX(GROUP_CONCAT(CAST(price AS CHAR) ORDER BY price DESC), ',', 1 ) AS `high`,
			SUBSTRING_INDEX(GROUP_CONCAT(CAST(price AS CHAR) ORDER BY price), ',', 1 ) AS `low`,
			SUBSTRING_INDEX(GROUP_CONCAT(CAST(price AS CHAR) ORDER BY created_at DESC), ',', 1 ) AS `close`,
			SUM(qty) AS volume  
		  FROM trades
		  WHERE pair = '$pair'
		  AND UNIX_TIMESTAMP(`created_at`) > ($start)
		  GROUP BY market_id, buckettime 
		  ORDER BY buckettime ASC
	  "));
		
	}
}