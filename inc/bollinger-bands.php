<?php
/* BOLLINGER BAND
    returns an numeric value for the various band parameters (mid, high, low) using price data caluclated on 20 days
    http://stockcharts.com/school/doku.php?st=bollinger&id=chart_school:technical_indicators:bollinger_bands
  */
    class BollingerBands{

        public $version = '1.0';

        var $price_array = array();
        var $period = 20;//days
        var $sma20 = 0;//initial SIMPLE MOVING AVERAGE

        var $standard_deviation20 = 0; //standard deviation

        function __construct($arr = array() ){
            $this->price_array = $arr;
        }

        function set_price_array($arr){

            $count = count($arr);

            if($count > $this->period){

                $offset = $count - $this->period;
                $this->price_array = array_splice($arr, $offset);
            }else{
                $this->price_array = $arr;    
            }

        }

        function get_price_array(){
            return $this->price_array;            
        }
        /* bollinger bands are calculated on 20 days */
        function is_valid_count(){
            if(count($this->price_array) >= $this->period){
                return true;
            }else{
                return false;
            }   
        }

        function get_standard_deviation(){
               //  https://www.mathsisfun.com/data/standard-deviation-formulas.html

      //measured slots is the amount of total population, if arr length doesn't meat mesured slots, apply Bessel's correction
      //array for step 2:
          $arr2 = array();
          $mean = $this->get_mid_band();

          for($i=0;$i<count($this->price_array);$i++){
              $x = $this->price_array[$i];

              $val = pow ( ($x - $mean) , 2);
              array_push($arr2,$val);
          }
      //step3 

          if(count($arr2) < $this->period){
        //apply bessel's correction
            $variance = array_sum($arr2)/(count($arr2) - 1);
        }else{
            $variance = array_sum($arr2)/$this->period;
        }

        $this->standard_deviation20 = sqrt($variance);
        return $this->standard_deviation20;
    }

    function get_upper_band(){
        return $this->sma20 + ($this->get_standard_deviation() * 2);
    }

    function get_lower_band(){
        return $this->sma20 - ($this->get_standard_deviation() * 2);
    }

    function get_mid_band(){
        if($this->is_valid_count() ){
            $this->sma20 = array_sum($this->price_array)/count($this->price_array);
            return $this->sma20;
        }else{
            return 0;
        }

    }

}

?>