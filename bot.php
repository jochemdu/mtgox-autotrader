<?php
function clearscreen(){
	newt_init();
	newt_get_screen_size($cols, $rows);
	newt_finished();
	$rowscleared = 0;
	while($rowscleared < $rows){
		echo "\n";
		$rowscleared++;
	}
}


include("gox.class.php");
include("settings.php");

$gox = new Gox($APIKEY, $APISECRET);
$change = array(0, 0, 0, 0, 0);		// Array to hold changes in the average BTC value.
$ticker = $gox->ticker();		// Get the current ticker data.
$vwap = $ticker['ticker']['vwap']; 	// Get initial average price.
$run = 1;				// Iteration counter.
echo $APIKEY . ' ' . $APISECRET;
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
	$origionalbalance = $origional_btc_balance + ($origional_usd_balance*$vwap); //amount of money you had when you started the program, done here to account for variations in BTC/USD excahnge rate

	echo "BTC Balance: " . $btc_balance . "\nUSD Balance: " . $usd_balance . "\n";
	
	$high = $ticker['ticker']['high'];
	$low = $ticker['ticker']['low'];
	$last = $ticker['ticker']['last'];

	$change[0] = $change[1];
	$change[1] = $change[2];
	$change[2] = $change[3];
	$change[3] = $change[4];	

	$oldvwap = $vwap;
	$vwap = $ticker['ticker']['vwap']; // Average price
	$width = $high - $low;


	
	$change[4] = $vwap-$oldvwap;

	$balance = $btc_balance + ($usd_balance*$vwap); //amount of money you have
	$profit = $balance - $origionalbalance;
	
	passthru(clear);
	
	echo "BTC Balance: " . $btc_balance . "\nUSD Balance: $" . $usd_balance . "\n";
        echo "\nAverage: $". $vwap . "\nHigh: $" . $high . "\nLow: $" . $low . "\nLast: $" . $last;
        echo "\nRun: " . $run;
        echo "\nPrice Change: " . $change[0] . ', ' . $change[1] . ', ' . $change[2] . ', ' . $change[3] . ', ' . $change[4] ;
        echo "\nProfit: " . $profit;

	if($change[0] > $change[1] && $change[1] > $change[2] && $change[2] > $change[3] && $change[3] > $change[4] ){
		var_dump($gox->sellBTC($btc_balance,$vwap,"BTC" . $vwap . " per BTC."));
		echo("\nSold " . $btc_balance . "BTC at $");
		
	}
	
	if($change[0] < $change[1] && $change[1] < $change[2] && $change[2] < $change[3] && $change[3] < $change[4] ){
		$gox->buyBTC($usd_balance/$vwap,$vwap,"BTC");
		echo("\nBought " . $usd_balance/$vwap . "BTC at $" . $vwap . " per BTC.");
		//echo "\noldvwap*1.05: " . $oldvwap*1.05;
	}
	
	$run++;
	echo("\n\n To donate BTC: 14DFnrDrAHCfvoh6QmzZPKmgBpsD37xrGM");
	sleep($UPDATE_INTERVAL);
}
?>

