#!/usr/bin/env php
<?php
function clearscreen(){
	$rows=100;
	$rowscleared = 0;
	while($rowscleared < $rows){
		echo "\n";
		$rowscleared++;
	}
}


include("gox.class.php");
include("settings.php");

$gox = new Gox($APIKEY, $APISECRET);
$change = array(0, 0, 0, 0, 0);				// Array to hold changes in the average BTC value.
$ticker = $gox->ticker();					// Get the current ticker data.
$vwap = $ticker['ticker']['vwap']; 			// Get initial average price.
$run = 1;									// Iteration counter.

//exit if APIKEY or APISECRET is not given
if($APIKEY == ''){ 
	echo "FILL IN YOUR API KEY AND SECRET";
	exit();
}

$info = $gox->getInfo();
$origional_btc_balance = floatval($info['Wallets']['BTC']['Balance']['value']);
$origional_usd_balance = floatval($info['Wallets']['USD']['Balance']['value']);




while(true){
	$info = $gox->getInfo();
	$ticker = $gox->ticker();
	$btc_balance = floatval($info['Wallets']['BTC']['Balance']['value']);
	$usd_balance = floatval($info['Wallets']['USD']['Balance']['value']);

	
	
	$high = $ticker['ticker']['high'];
	$low = $ticker['ticker']['low'];
	$last = $ticker['ticker']['last'];		

	$oldvwap = $vwap;
	$vwap = $ticker['ticker']['vwap']; 		// Average price
	$change[0] = $change[1];
	$change[1] = $change[2];
	$change[2] = $change[3];
	$change[3] = $change[4];
	$change[4] = $vwap-$oldvwap;

	$origionalbalance = ($origional_btc_balance*$vwap) + $origional_usd_balance; 	//amount of money (in USD) you had when you started the program, done here to account for variations in BTC/USD excahnge rate
	$balance = ($btc_balance*$vwap) + $usd_balance; 								//amount of money you have (in USD)
	$profitUSD = $balance - $origionalbalance;
	$profitBTC = $profitUSD / $vwap;
	
	clearscreen();

	echo "BTC Balance: " . $btc_balance . "\nUSD Balance: $" . $usd_balance . "\n";
        echo "\nAverage: $". $vwap . "\nHigh: $" . $high . "\nLow: $" . $low . "\nLast: $" . $last;
        echo "\nRun: " . $run;
        echo "\nPrice Change: " . $change[0] . ', ' . $change[1] . ', ' . $change[2] . ', ' . $change[3] . ', ' . $change[4] ;
        echo "\nProfit in USD: " . $profitUSD . "\nProfit in BTC: " . $profitBTC;

	if($change[0] > $change[1] && $change[1] > $change[2] && $change[2] > $change[3] && $change[3] > $change[4] ){
		var_dump($gox->sellBTC($btc_balance,$vwap,"BTC" . $vwap . " per BTC."));
		echo("\nSold " . $btc_balance . "BTC at $");
		
	}
	
	if($change[0] < $change[1] && $change[1] < $change[2] && $change[2] < $change[3] && $change[3] < $change[4] ){
		$gox->buyBTC($usd_balance/$vwap,$vwap,"BTC");
		echo("\nBought " . $usd_balance/$vwap . "BTC at $" . $vwap . " per BTC.");
	}
	
	$run++;
	sleep($UPDATE_INTERVAL);	
}
?>