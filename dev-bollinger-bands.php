<?php
//testing
require_once('inc/functions-math.php');
require_once('inc/bollinger-bands.php');
?>
<h2>testing</h2>
SMA/EMA based off of CLOSING PRICE OF THE DAY


<?php
//testing
$price_arr = array(50,49,24,70,32);
$price_arr2 = array(50,49,24,70,32,35,24,60,30,36,24,75,23,65,35,57,43,65,24,45,24,54,24,67,62,50,65,57,32);
?>
<p>elements: <?php echo implode(', ', $price_arr);?></p>
<p>Standard Deviation (full count): <?php echo get_standard_deviation($price_arr,5); ?></p>
<p>Standard Deviation (sample count): <?php echo get_standard_deviation($price_arr,10); ?></p>
<hr>
<p>price_arr2: <?php echo implode(',' , $price_arr2); ?></p>
<p>EMA12 &amp; EMA26 are popular SHORT TERM trends</p>
<p>EMA50 &amp; EMA200 are popular LONG TERM trends</p>
<p>count for price arr2: <?php echo count($price_arr2);?></p>
<p>ema12: <?php echo get_ema(12,$price_arr2,  45, 46.6); ?></p>
<p>ema26: <?php echo (get_ema(26,$price_arr2, 45, 46.6)); ?></p>


<?php 
$bollinger_bands = new BollingerBands;
$bollinger_bands->set_price_array($price_arr2);
?>
<h2>Bollinger Bands <?php echo $bollinger_bands->version;?></h2>
<p>Developed by John Bollinger, Bollinger Bands® are volatility bands placed above and below a moving average. Volatility is based on the standard deviation, which changes as volatility increases and decreases. The bands automatically widen when volatility increases and narrow when volatility decreases. This dynamic nature of Bollinger Bands also means they can be used on different securities with the standard settings. For signals, Bollinger Bands can be used to identify M-Tops and W-Bottoms or to determine the strength of the trend. </p>
<p>They use SMA and standard deviation calcuation based on 20 days</p>

<?php 
//set the price array
echo 'Bolinger Bands valid count: '.$bollinger_bands->is_valid_count(); 
echo '<br>upper: '.$bollinger_bands->get_upper_band();
echo '<br>lower: '.$bollinger_bands->get_lower_band();
echo '<br>Mid: '.$bollinger_bands->get_mid_band();

?>
