<?php
/*
DEFINE YOUR DETAILS HERE
apikey & secret: from Bittrex https://bittrex.com/home/api
//minimum amount minimum traded in USD.  If below this, we ignore from coinmarketcap
// target_min_pct_loss   //we are looking for discounted coins and therefore would look to find those that are a bit down
target_profit_pct: our target profit_percentages
//daily_trading_pct: percentage amount to allow for day trading.  (.8 = 80%)
  'apikey'    => '1234567890',
  'apisecret' =>  '1234567890',
*/
$preferences = array(

  'apikey'    => '1234567890',
  'apisecret'   =>  '1234567890',
  'min_vol_usd' => 500000,
  'target_min_pct_loss' => 4,
  'target_profit_pct'  => 4,
  'daily_trading_pct' => .8
);


//BITTREX TRADING FEE
$commission_pct = 0.25;
?>