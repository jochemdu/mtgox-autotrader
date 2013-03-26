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

echo "mtgox-autotrader \n";
echo "https://github.com/theblazehen/mtgox-autotrader \n\n";

include("gox.class.php");
include("settings.php");

//exit if APIKEY or APISECRET is not given
if($APIKEY == ''){
        echo "WARNING: FILL IN YOUR API KEY AND SECRET.\n";
        exit();
}

$gox = new Gox($APIKEY, $APISECRET);
$change = array(0, 0, 0, 0, 0);				// Array to hold changes in the average BTC value.
$ticker = $gox->ticker();					// Get the current ticker data.
$vwap = $ticker['ticker']['vwap']; 			// Get initial average price.
$run = 1;									// Iteration counter.

$info = $gox->getInfo();
$origional_btc_balance = floatval($info['Wallets']['BTC']['Balance']['value']);
$origional_usd_balance = floatval($info['Wallets']['USD']['Balance']['value']);
fwrite($fh, "confidence,profitUSD");


$fh = fopen($file, 'w');
fclose($fh);


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
	for($foo = 0; $foo <= $records; $foo++){
		$change[$foo] = $change[($foo + 1)];
	}
	$change[$records] = $vwap-$oldvwap;

	$confidencecount = 0;
	for($foo = 0;$foo < $records; $foo++){
		if($change[$foo] > 0){
			$confidencecount++;		
		}
		if($change[$foo] < 0){
			$confidencecount--;
		}
	}
	$confidence = ($confidencecount / $records) * 100;

	$origionalbalance = ($origional_btc_balance*$vwap) + $origional_usd_balance; 	//amount of money (in USD) you had when you started the program, done here to account for variations in BTC/USD excahnge rate
	$balance = ($btc_balance*$vwap) + $usd_balance; 								//amount of money you have (in USD)
	$profitUSD = $balance - $origionalbalance;
	$profitBTC = $profitUSD / $vwap;
	
	clearscreen();

	echo "BTC Balance: " . $btc_balance . "\nUSD Balance: $" . $usd_balance . "\n";
        echo "\nAverage: $". $vwap . "\nHigh: $" . $high . "\nLow: $" . $low . "\nLast: $" . $last;
        echo "\nRun: " . $run;
        echo "\nPrice Change: " . $change[0] . ', ' . $change[1] . ', ' . $change[2] . ', ' . $change[3] . ', ' . $change[4] . ', ' . $change[5];
        echo "\nProfit in USD: " . $profitUSD . "\nProfit in BTC: " . $profitBTC;
        echo "\nConfidencecount: " . $confidencecount . "\nConfidence: " . $confidence . "\n";
        $minus20 = 0 -20;
	if($confidence < -20.0){
		$gox->sellBTC($btc_balance,$vwap,"BTC" . $vwap . " per BTC.");
		echo("\nSold " . $btc_balance . "BTC at $" . $vwap);
		
	}
	
	if($confidence > 20.0){
		$gox->buyBTC($usd_balance/$vwap,$vwap,"BTC");
		echo("\nBought " . $usd_balance/$vwap . "BTC at $" . $vwap . " per BTC.");
	}
	
	$run++;
	$fh = fopen($file, 'a');
	$logdata = $confidence . "," . $profitUSD . "\n" ;
	fwrite($fh, $logdata);
	fclose($fh);
	sleep($UPDATE_INTERVAL);	
}
?>
