<?php
  /* HISTORICAL DATA FROM CRYTOCOMPARE 
    https://www.cryptocompare.com/api/#-api-data-histoday-
    https://min-api.cryptocompare.com/
 */ 
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

    $cryptoApiURL = 'https://min-api.cryptocompare.com/data/'.$endpoint.'?fsym=' .$symbol. '&tsym=' .$desired_currency. '&limit=' .$timeslots. '&e=' .$exchange;

    return json_decode(file_get_contents($cryptoApiURL), true);

  }
?>