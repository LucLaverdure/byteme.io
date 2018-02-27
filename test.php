<?php
	// /kunden/homepages/0/d220355082/htdocs/bitme/core/phantom-f/bin
	require("/kunden/homepages/0/d220355082/htdocs/bitme/core/phantomjs/vendor/autoload.php");

	use JonnyW\PhantomJs\Client;

	$client = Client::getInstance();
	$client->getEngine()->setPath("/kunden/homepages/0/d220355082/htdocs/bitme/core/phantom-f/bin/phantomjs");
	
	$client->getEngine()->addOption('--ssl-protocol=any');
	$client->getEngine()->addOption('--ignore-ssl-errors=true');
	$client->getEngine()->addOption('--web-security=false');
	$client->getEngine()->addOption('--debug=true');
	$client->getEngine()->addOption('--cookies-file=cookie.txt');
	
	$client->getEngine()->debug(true);

	$request = $client->getMessageFactory()->createRequest();
	$response = $client->getMessageFactory()->createResponse();

	$request->setMethod('GET');
	$request->setUrl('http://perdu.com');
		
	$client->send($request, $response);

	var_dump($client->getLog());
	
	var_dump($response->getStatus());
	var_dump($response->getContent());
	
	if($response->getStatus() === 200) {
		$resp = $response->getContent();
		var_dump($resp);
	}

?>