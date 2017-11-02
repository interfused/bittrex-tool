<?php
//BITTREX
//Building A Simple Bittrex Bot.  We are looking at day trading
require_once('functions-math.php');
$apikey='96f1ea746eed477b81b195bbb5675611';
$apisecret='5e98fb64b67a414487fbc4fc1ec96d9b';

//coinmarketcap
//amount minimum traded in USD
$min_vol_usd = 500000;

//we are looking for discounted coins and therefore would look to find those that are a bit down
$target_min_pct_loss = 4;

//our target profit_percentages
$target_profit_pct = 4;

//BITTREX TRADING FEE
$commission_pct = 0.25;

?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<title>Bitrex Trading</title>
</head>

<body>
<?php
/* FORMAT BTC */
function formatBTCString($num){
	return number_format($num,10);
	//return rtrim(rtrim(sprintf('%.10F', $var), '0'), ".");
}

/* HISTORICAL DATA */ 
function getHistoricalData($symbol,$desired_currency,$frequency){
	$htmlStr = '';
    $exchange = 'CCCAGG';
	switch($symbol){
		case "MIOTA":
			$symbol = 'IOTA';
		break;
	}
	switch($frequency){
		case "HOURLY":
		$endpoint = 'histohour';
	$timeslots = 24;
		break;
		default:
		$endpoint = 'histoday';
	$timeslots = 7;
		break;
	}
	
    /*
	//https://www.cryptocompare.com/api/#-api-data-histoday-
    //https://min-api.cryptocompare.com/
    */
	$cryptoApiURL = 'https://min-api.cryptocompare.com/data/'.$endpoint.'?fsym=' .$symbol. '&tsym=' .$desired_currency. '&limit=' .$timeslots. '&e=' .$exchange;
	
	return json_decode(file_get_contents($cryptoApiURL), true);

}

/* RETURN AN ARRAY OF AVERAGE TARGETS */
function getPricingTargets($symbol){
	$target_arr = array(
	'low' => 0,
	'high' => 0,
	'spread_pct' => 0,
	);
	
	$history = getHistoricalData($symbol,'BTC','HOURLY');

if($history['Response']==='Success'){
	$recCnt = count($history['Data']);
	//echo '<pre>';
	//print_r($history);
	//echo '</pre>';
	$avg_open_arr = array();
	$avg_low_arr = array();
    $avg_high_arr = array();
    $avg_close_arr = array();
	$avg_spread_arr = array();
	$avg_spread_pct_arr = array();
	
    $avg_low_amt = 0;
    $avg_high_amt = 0;
	$avg_spread_pct_amt=0;
	
	for($i=0;$i<$recCnt;$i++ ){
		$node = $history['Data'][$i];
		
		array_push($avg_open_arr,$node['open']);
		array_push($avg_low_arr,$node['low']);
		array_push($avg_high_arr,$node['high']);
		array_push($avg_close_arr,$node['close']);
		array_push($avg_spread_arr, ($node['high'] - $node['low']) );
		array_push($avg_spread_pct_arr, number_format(getPercentageDifference($node['low'],$node['high']),2 ) );
		
		
		echo 'open: '.formatBTCString($avg_open_arr[$i]);
		echo 'low: '.formatBTCString($avg_low_arr[$i]);
		echo ' high: '.formatBTCString($node['high']);
		echo 'close: '.formatBTCString($avg_close_arr[$i]);
		echo ' spread: '.formatBTCString($avg_spread_arr[$i]);
		echo ' spread_pct: '.($avg_spread_pct_arr[$i]);
		echo '<br><br>';
	}//end for loop
	$target_arr['low'] = get_average($avg_low_arr);
	$target_arr['high'] = get_average($avg_high_arr);
	$target_arr['spread_pct'] = get_average($avg_spread_pct_arr);

}else{
	//something went wrong
	mail('jeremy@interfusedcreative.com','ERROR ON BITTREX TRADING TOOL','something went wrong with the bittrex trading tool for symbol '.$symbol);
}

return $target_arr;

}
///end getPricingTargets

/* checks to see if there is an existing open buy order*/
function bittrexOpenOrder($apikey, $apisecret, $symbol){
    $nonce=time();
 $uri='https://bittrex.com/api/v1.1/market/getopenorders?apikey='.$apikey.'&market=BTC-'.$symbol.'&nonce='.$nonce;
    $sign=hash_hmac('sha512',$uri,$apisecret);
    $ch = curl_init($uri);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $execResult = curl_exec($ch);
    $obj = json_decode($execResult, true);
    return $obj["result"];
}

/* get order history for a currency*/
function bittrexOrderHistory($apikey, $apisecret, $symbol){
    $nonce=time();
 $uri='https://bittrex.com/api/v1.1/account/getorderhistory?apikey='.$apikey.'&market=BTC-'.$symbol.'&nonce='.$nonce;
    $sign=hash_hmac('sha512',$uri,$apisecret);
    $ch = curl_init($uri);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $execResult = curl_exec($ch);
    $obj = json_decode($execResult, true);
    return $obj["result"];
}


/* GETTING CURRENT TICKER OF A SYMBOL  */
function bittrexticker($apikey,$apisecret,$baseCurrency='BTC',$symbol){
	$nonce = time();
	$uri = 'https://bittrex.com/api/v1.1/public/getticker?market='.$baseCurrency.'-'.$symbol;
	$sign=hash_hmac('sha512',$uri,$apisecret);
    $ch = curl_init($uri);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $execResult = curl_exec($ch);
    
	$obj = json_decode($execResult, true);
    $balance = $obj["result"]["Last"];
	return $balance;
}
/* GET ALL BALANCES */
function bittrexbalances($apikey, $apisecret){
    $nonce=time();
    $uri='https://bittrex.com/api/v1.1/account/getbalances?apikey='.$apikey.'&nonce='.$nonce;
    $sign=hash_hmac('sha512',$uri,$apisecret);
    $ch = curl_init($uri);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $execResult = curl_exec($ch);
    $obj = json_decode($execResult, true);
    $balances = $obj["result"];
    return $balances;
}

/* GET SINGULAR BITTREX BALANCE IN BTC */
function bittrexbalance($apikey, $apisecret,$currency='BTC'){
    $nonce=time();
    $uri='https://bittrex.com/api/v1.1/account/getbalance?apikey='.$apikey.'&currency='.$currency.'&nonce='.$nonce;
    $sign=hash_hmac('sha512',$uri,$apisecret);
    $ch = curl_init($uri);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $execResult = curl_exec($ch);
    $obj = json_decode($execResult, true);
    $balance = $obj["result"]["Available"];
    return $balance;
}

function bittrexbuy($apikey, $apisecret, $symbol, $quant, $rate){
    $nonce=time();
    $uri='https://bittrex.com/api/v1.1/market/buylimit?apikey='.$apikey.'&market=BTC-'.$symbol.'&quantity='.$quant.'&rate='.$rate.'&nonce='.$nonce;
    $sign=hash_hmac('sha512',$uri,$apisecret);
    $ch = curl_init($uri);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
    $execResult = curl_exec($ch);
    $obj = json_decode($execResult, true);
    return $obj;
}

function bittrexsell($apikey, $apisecret, $symbol, $quant, $rate){
    $nonce=time();
    $uri='https://bittrex.com/api/v1.1/market/selllimit?apikey='.$apikey.'&market=BTC-'.$symbol.'&quantity='.$quant.'&rate='.$rate.'&nonce='.$nonce;
    $sign=hash_hmac('sha512',$uri,$apisecret);
    $ch = curl_init($uri);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
    $execResult = curl_exec($ch);
    $obj = json_decode($execResult, true);
    return $obj;
}


?>
<div id="wallets">
<h1>Wallets</h1>


</div>

<div id="buying">
<h1>Buying Considerations</h1>
<?php
//fetch top 50 cryptos by marketcap
$cnmkt = "https://api.coinmarketcap.com/v1/ticker/?limit=50";
$fgc = json_decode(file_get_contents($cnmkt), true);
//echo '<pre>';
//print_r($fgc);
//echo '</pre>';
$counter = 0;
for($i=0;$i<50;$i++){
	
	$symbol = $fgc[$i]["symbol"];
			//MUST NOT HAVE ANY OPEN ORDERS PRIOR TO BUYING
				$openOrders = bittrexOpenOrder($apikey, $apisecret, $symbol);
				//print_r($openOrders);
		$allowBuy = $openOrders? false : true;
		
		
	if($allowBuy == false || $symbol=='MIOTA'){
		//EXIT CURENT FOR ITEM AND DO NEXT
		continue;
	}
    if($counter < 3){
        //check percentage change over last 7 days
        $percCng_7d = $fgc[$i]["percent_change_7d"];
		$percCng_24h = $fgc[$i]["percent_change_24h"];
		$vol = $fgc[$i]["24h_volume_usd"];
		
        if($percCng_7d <= $target_min_pct_loss && $percCng_24h < 1 && $vol >= $min_vol_usd){
            
			echo '<h2>Symbol to consider buying: '.$symbol.' with vol: '.$vol.'</h2>';
			
			

$target = getPricingTargets($fgc[$i]["symbol"]);
?>


<h2>Averages for <?php echo $fgc[$i]["symbol"];?></h2>

<p>low: <?php echo formatBTCString($target['low']);?></p>
<p>high: <?php echo formatBTCString($target['high']);?></p>
<p>spread pct: <?php echo $target['spread_pct'];?></p>
<?php
			
			//getHistoricalData($symbol,'BTC',7);
			if($target['spread_pct'] > $target_profit_pct  ){
		//BUY THE SYMBOL
			//FIND THE LOWEST COST
			$cost = min($target['low'] , $fgc[$i]["price_btc"], bittrexticker($apikey,$apisecret,'BTC',$fgc[$i]["symbol"]) ) ;
			
            //fetch bittrex btc balance
            $balance = bittrexbalance($apikey, $apisecret,'BTC');
            //calc 1/5th of available
            $fifthBal = $balance / 5;
            //calc how much coin to buy
            $amounttobuy = $fifthBal / $cost;
	 		
		
			
			if($allowBuy && ($fifthBal > 0.0005) && $symbol != 'XRP' ){
				echo 'cost is: '.$cost.' and amount to buy: '.$amounttobuy;
			
				//$buy = bittrexbuy($apikey, $apisecret, $symbol, $amounttobuy, $cost);
            echo '<hr>';
			echo '<pre>';
			//print_r($buy);
			echo '</pre>';
			}
            
            $counter++;
			
		//echo 'BUY: '.$fgc[$i]["symbol"].' cost: '.$cost.' amt: '.$amounttobuy;
		echo '<hr>';
	}
            
        }   
    }
}


?>
</div>

<div id="selling">
<h1>Selling Considerations</h1>
<?php
//GET BALANCES
$balances = bittrexbalances($apikey,$apisecret);
	
	for($i=0;$i<count($balances);$i++){
		//WE ARE IGNORING BITCOIN FOR SELLING PURPOSES
		if($balances[$i]['Currency'] != 'BTC' && $balances[$i]['Available']>0){
			$symbol = $balances[$i]['Currency'];
			echo '<h3>balance for '.$symbol.'</h3>';
			echo '<pre>';
	print_r($balances[$i]);
	echo '</pre>';
		//check to see if there is an existing sell order
		$openOrders = bittrexOpenOrder($apikey, $apisecret, $balances[$i]['Currency']);
		$allowSell = true;
		foreach($openOrders as $openOrder){
			if($openOrder['OrderType'] == 'LIMIT_SELL' ){
				$allowSell = false;
				break;
			}
		}
		if($allowSell == false){
			continue;
		}
		echo '<br><br>allow sell: '.$allowSell;
		
		if($allowSell == true){
			//FIND THE LAST BUY PRICE TO SET APPRORPIATE SELL PRICE
			$orderHistories = bittrexOrderHistory($apikey, $apisecret, $balances[$i]['Currency']);
			if(count($orderHistories[0]) > 1){
			
			print_r($orderHistories);
			//SELL PRICE IN BTC
			foreach($orderHistories as $orderHistory){
				if($orderHistory['OrderType'] == 'LIMIT_BUY' ){
					$sellprice = $orderHistory['Limit'] * (1+ $target_profit_pct/100) ;
					break;
				}
			}
			echo '<br><br>s1: '.$sellprice.' s2: '.bittrexticker($apikey,$apisecret,'BTC',$balances[$i]['Currency']);
			$sellprice = max($sellprice,bittrexticker($apikey,$apisecret,'BTC',$balances[$i]['Currency']));
			echo '<br>final sell price:' .$sellprice;
			//we will consider selling 80%
			$amounttosell = $balances[$i]['Balance']* .8;
			//$sell = bittrexsell($apikey, $apisecret, $symbol, $amounttosell, $sellprice);
			echo '<hr>';
			echo '<pre>';
			//print_r($sell);
			echo '</pre>';
		}
		}
			
			
		}
	}
	
	
	
?>
</div>

</body>
</html>
