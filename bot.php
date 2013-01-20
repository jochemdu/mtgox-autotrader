<?php
include("gox.class.php");
$gox = new Gox('apikey', 'apisecret');

$vwap = "100.0";
$run = "0";

while(true){
	$run = $run + "1";
	$info = $gox->getInfo();
	$ticker = $gox->ticker();
	$btc_balance = floatval($info['Wallets']['BTC']['Balance']['value']);
	$usd_balance = floatval($info['Wallets']['USD']['Balance']['value']);
	/*give balances*/ echo("BTC Balance: " . $btc_balance . "\nUSD Balance: " . $usd_balance . "\n");

	$high = $ticker['ticker']['high'];
	$low = $ticker['ticker']['low'];
	$last = $ticker['ticker']['last'];

	$oldvwap = $vwap;
	$vwap = $ticker['ticker']['vwap']; //average price
	$width = $high - $low;
	$girth = 1 - ($low / $high);
	$high_scrape = ( $high - $last ) / $width;
	$low_scrape  = ( $last - $low ) / $width;
	$change = $vwap-$oldvwap;
	passthru(clear);
	echo("BTC Balance: " . $btc_balance . "\nUSD Balance: $" . $usd_balance . "\n");
	echo("\nAverage: $". $vwap . "\nHigh: $" . $high . "\nLow: $" . $low . "\nLast: $" . $last);
	echo "\nRun: " . $run . "\nPrice Change: " . $change ;
	

	if($vwap > $oldvwap*1.01){
		var_dump($gox->sellBTC($btc_balance,$vwap,"BTC" . $vwap . " per BTC."));
		echo("\nSold " . $btc_balance . "BTC at $");
		//echo "$oldvwap*1.05: " . $oldvwap*1.05;
	}
	if($vwap < $oldvwap*1.01){
		$gox->buyBTC($usd_balance/$vwap,$vwap,"BTC");
		echo("\nBought " . $usd_balance/$vwap . "BTC at $" . $vwap . " per BTC.");
		//echo "\noldvwap*1.05: " . $oldvwap*1.05;
	}
	echo("\n\n To donate BTC: 14DFnrDrAHCfvoh6QmzZPKmgBpsD37xrGM")
	sleep(360);
}

?>
