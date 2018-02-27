<?php
// Home
class homepage extends Controller {
	// Display function: validate urls to activate the controller
	function validate() {
		// Activate home controller for /home and /home/*
		if (q()=="access" || q()=="home" || q()=="/" || q()=="") {
			return 1;	// priority 1
		}
		else return false;
	}

	function execute() {
		$this->addModel('whatismy', 'ip', $_SERVER['REMOTE_ADDR']);
		$this->loadView('home.tpl');
	}
}


// People View
class peoplepage extends Controller {
	// Display function: validate urls to activate the controller
	function validate() {
		// Activate home controller for /home and /home/*
		if (q()=="people") {
			return 1;	// priority 1
		}
		else return false;
	}

	function execute() {
		$this->loadView('people.tpl');
	}
}

// People View
class logclose extends Controller {
	// Display function: validate urls to activate the controller
	function validate() {
		// Activate home controller for /home and /home/*
		if (q()=="close") {
			return 1;	// priority 1
		}
		else return false;
	}

	function execute() {
		$this->loadView('closeme.tpl');
	}
}

class search_radio_can extends Controller {

	function validate() {
		// Activate home controller for /home and /home/*
		if (q('0')=="search") {
			return 1;	// priority 1
		}
		else return false;
	}
	
	function some_curl($url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "$url");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		//curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: token  27a0b9a81f075cc991d8488bfa380b66d6405ec7"));
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"); 
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		return curl_exec($ch);
	}

	function execute() {	
		$this->loadView("search-results.tpl");
		
		$this->procure(array(
			"file_contents" => "https://duckduckgo.com?q=".q(1)."+site%3Aradio-canada.ca&ia=web",
			"filter" => "#links h2",
			"placeholder" => "div",
			"display" => "html",
			"mode" => "append",
			"javascript" => "yes"
		));
		
	}

}