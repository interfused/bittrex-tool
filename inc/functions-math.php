<?php
/* get averages for an array of numbers */
function get_average($arr){
	$total=0;
	for ($i=0;$i<count($arr);$i++){
		if(is_nan($arr[$i]) ){
			continue;
		}
		$total += $arr[$i];
	}
	return $total/count($arr);
}

/* return the percentage difference tween two numbers */
  function getPercentageDifference($a,$b){
	  if(is_nan($a) || is_nan($b)  ){
		  return 0;
	  }

    if($a==$b){
      return 0;
    }
    $diff = abs($b-$a);
    $percentageDifference = ($diff/$a)*100;

    if($a > $b){
      return $percentageDifference*(-1);
    }else{
      return $percentageDifference;
    }

  }
  
  /** STANDARD DEVIATION 
  https://www.mathsisfun.com/data/standard-deviation-formulas.html
  returns a numeric value
  **/
  
  function get_standard_deviation($arr,$measured_slots){
	  //measured slots is the amount of total population, if arr length doesn't meat mesured slots, apply Bessel's correction
	  //array for step 2:
	  $arr2 = array();
	  $mean = get_average($arr);
	  
	  for($i=0;$i<count($arr);$i++){
		  $x = $arr[$i];
		  if(is_nan($x)){
			  continue;
		  }
		  $val = pow ( ($x - $mean) , 2);
		  array_push($arr2,$val);
	  }
	  //step3 
	  if(count($arr) > $measured_slots){
	  	$measured_slots = count($arr);
	  }

	  if(count($arr2) < $measured_slots){
	  	//apply bessel's correction
	  	$variance = array_sum($arr2)/(count($arr2) - 1);
	  }else{
	  	$variance = get_average($arr2);
	  }
	  
	  return sqrt($variance);
  }
  /* RETURNS EXPONENTIAL MOVING AVERAGE BASED OFF OF ARRAY OF NUMBRS 
	http://stockcharts.com/school/doku.php?id=chart_school:technical_indicators:moving_averages
	arr: array of numbers 
	period: period of time in days: (EX: 12,26,50,100)
  */
  function get_ema($period, $arr, $closePrice, $ema_previous_day=0){
  	$arr2 = $arr;
  	$count = count($arr);

  	//array size must be greater than period
  	if($count < $period){
  		return 0;
  	}

  	if($count > $period){
  		$offset = $count - $period;
  		$arr2 = array_splice($arr, $offset);
  	}
  	//
  	$initial_sma = get_average($arr2);
  	$multiplier = 2/($period + 1);
  	$ema = ($closePrice - $ema_previous_day) * $multiplier + $ema_previous_day;
  	
  	return $ema;
  }


?>
