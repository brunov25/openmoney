<?php

namespace openmoney;

use Tonic;
use Tonic\Resource,
Tonic\Response;



/**
 * The @uri annotation routes requests that match that URL to this resource. Multiple
 * annotations allow this resource to match multiple URLs.
 * curl -v -u username:password http://openmoneyroudleo.com/rest/settings
 *
 * @uri /settings
 */
class settings extends Resource
{


	private $user;
	private $username;

	/**
	 * The setup() method is called when the resource is executed. We don't do this check
	 * within the resource constructor as we can't cleanly throw an exception from within
	 * an object constructor.
	 */
	function setup() {
		require("rest_connect.php");
		require("../password.php");
		if(isset($_SERVER['PHP_AUTH_USER'])&&isset($_SERVER['PHP_AUTH_PW'])){

			//echo "Attempting to authenticate user.\n";
			$users_q = mysqli_query($db,"SELECT * FROM users WHERE user_name='" . mysqli_real_escape_string($db,$_SERVER['PHP_AUTH_USER']) ."'");
			$users = mysqli_fetch_array($users_q);
			//echo "username:".$users['user_name']."\n";
			//echo "password:".$_SERVER['PHP_AUTH_PW']."\n";
			//echo "check1".password_verify($_SERVER['PHP_AUTH_PW'],$users['password2'])."\n";
			//echo "check2".password_verify($_SERVER['PHP_AUTH_PW'],$users['password'])."\n";
			if(password_verify($_SERVER['PHP_AUTH_PW'],$users['password2']) OR (password_verify($_SERVER['PHP_AUTH_PW'], $users['password']))){
				//echo "verified\n";
				$this->user = $users;
				$this->username = $users['user_name'];
			} else {
				throw new Tonic\UnauthorizedException;
			}
		} else {
			throw new Tonic\UnauthorizedException;
		}
	}
	/**
	 * Use this method to handle GET HTTP requests.
	 *
	 * The optional :name parameter in the URL available as the first parameter to the method
	 * or as a property of the resource as $this->name.
	 *
	 * Method can return a string response, an HTTP status code, an array of status code and
	 * response body, or a full Tonic\Response object.
	 *
	 * @method GET
	 * @provides application/json
	 * @json
	 * @cache 0
	 * @return Tonic\Response
	 */
	public function saySettings()
	{

		require("rest_connect.php");

		$result_array = array();
		$userSpaces_q = mysqli_query($db,$test = "SELECT *, us.id usId FROM user_spaces us, spaces s WHERE s.id = us.space_id AND us.user_id = '" . $this->user['id'] . "' ORDER BY us.id ASC") or die($test . mysqli_error($db));
		while($userSpaces = mysqli_fetch_array($userSpaces_q)){
			array_push($result_array,array("id"=>intval($userSpaces['usId']),"space_name"=>$userSpaces['space_name']));
		}
			
		$result = new Response(200, $result_array);

		return $result;
	}


	/**
	 * Use this method to handle GET HTTP requests.
	 *
	 * The optional :name parameter in the URL available as the first parameter to the method
	 * or as a property of the resource as $this->name.
	 *
	 * Method can return a string response, an HTTP status code, an array of status code and
	 * response body, or a full Tonic\Response object.
	 * 
	 * example test script
	 * curl -u username:password -i -H "Content-Type: application/json" -X POST -d '{"trading_name":"my_new_name","currency":"cc","user_space_id":682}' 'http://openmoney.proudleo.com/rest/settings/'
	 *
	 * @method POST
	 * @accepts application/json
	 * @provides application/json
	 * @json
	 * @cache 0
	 * @return Tonic\Response
	 */
	public function saySettingsNew()
	{
	
		require("rest_connect.php");
		error_reporting(E_ERROR | E_WARNING | E_PARSE);
	
		$requestData = $this->request->data;
	
		$tradingName = '';
		if(isset($requestData->trading_name)){
			$tradingName = mysqli_real_escape_string($db, $requestData->trading_name);
		}
	
		$currencyName = '';
		if(isset($requestData->currency)){
			$currencyName = mysqli_real_escape_string($db, $requestData->currency);
		}
			
		$userSpaceId = '';
		if(isset($requestData->user_space_id)){
			$userSpaceId = intval(mysqli_real_escape_string($db, $requestData->user_space_id));
		}
		$uacID = 0;
		$currencyID = 0;
		$error = '';

		//user space query
		$userSpace_q = mysqli_query($db , $test = "SELECT * FROM user_spaces us, spaces s WHERE us.space_id = s.id AND us.id='$userSpaceId'") or die($test . mysqli_error($db));
		if ($userSpace = mysqli_fetch_array( $userSpace_q ) ) {

			if($tradingName != '') {
				//join currency / create trading name

				//check that there is not a dot in the trading name
				if ( ctype_alnum($tradingName) ) {
					//the dot was not found in the trading name
				
					//check that a currency by that name exists.
					$currency_q = mysqli_query($db , $test = "SELECT * FROM currencies c WHERE c.currency='$currencyName'") or die($test . mysqli_error($db));
					if ($currency = mysqli_fetch_array( $currency_q ) ) {

						//existing trading name check
						$trading_name = ($userSpace['space_name'] == '') ? $tradingName : $tradingName . "." . $userSpace['space_name'];
						$trading_name_check_q = mysqli_query($db , $test = "SELECT *, uac.id user_account_currencies_id FROM user_account_currencies uac, currencies c WHERE uac.currency_id=c.id AND c.currency = '" . $currency['currency'] . "' AND uac.trading_name = '" . $trading_name . "' ORDER BY uac.id ASC") or die($test . mysqli_error($db));
						if ($trading_name_check = mysqli_fetch_array( $trading_name_check_q ) ) {
							$error = "That trading name already exists!<br /> Please choose another trading name." ;
						} else {
							//existing currency name check
							$currencyID = $currency['id'];
							$currency_check_q = mysqli_query($db , $test = "SELECT * FROM currencies c WHERE c.currency='$tradingName' AND c.currency_steward != '".$this->user['id']."'") or die($test . mysqli_error($db));
							if ($currency_check = mysqli_fetch_array( $currency_check_q ) ) {
								$error = "A currency exists with that trading name!<br /> Please choose another trading name.";
							} else {
						
								//check if there is a subspace name that exists for that trading name.
								$subspace_name = ($userSpace['space_name'] == '') ? $tradingName : $tradingName . "." . $userSpace['space_name'];
								$subspace_check_q = mysqli_query($db , $test = "SELECT * FROM spaces s WHERE s.space_name='" . $subspace_name . "'") or die($test . mysqli_error($db)); 
								if ( $subspace = mysqli_fetch_array( $subspace_check_q ) ) {
									$error = "A subspace exists with that trading name!<br /> Please choose another trading name.";
								} else {
									//all conditions have been met insert trading name.
									mysqli_query($db , $test = "INSERT INTO user_account_currencies (trading_name, currency_id, user_space_id) VALUES ('$trading_name','" . $currency['id'] . "','$userSpaceId')") or die($test . mysqli_error($db));
									$uacID = mysqli_insert_id($db);
								}
						
							}
						}
						 
					} else {
						$error = 'The currency you selected does not exist!<br /> Please choose another currency.';	
					}
				} else {
					$error = "Alphanumeric characters are only allowed in the trading name!<br /> Please choose another trading name.";
				}
			} else {
				//create currency

				//check if there is a dot in the currency name
				if ( (strpos ($currencyName,".") === false) && (strpos ($currencyName," ") === false) ) {
					//a dot and space was not found.
					if (strlen ($currencyName) < 16) {
	
						//check if currency exists
						//check that a currency by that name exists.
						$currency_name = ($userSpace['space_name'] == '') ? $currencyName : $currencyName . "." . $userSpace['space_name'];
						$currency_q = mysqli_query($db , $test = "SELECT * FROM currencies c WHERE c.currency='" . $currency_name . "'") or die($test . mysqli_error($db));
						if ($currency = mysqli_fetch_array( $currency_q ) ) {
							//A currency by that name was found
							$error = "A currency exists with that name!<br /> Please choose another currency.";
						} else {
							$trading_name_check_q = mysqli_query($db , $test = "SELECT *, uac.id user_account_currencies_id FROM user_account_currencies uac, user_spaces us, currencies c WHERE uac.currency_id=c.id AND uac.trading_name = '$currency_name' AND us.id = uac.user_space_id AND us.user_id != '" . $this->user['id'] . "' ORDER BY uac.id ASC") or die($test . mysqli_error($db));
							if ($trading_name_check = mysqli_fetch_array( $trading_name_check_q ) ){
								$error = "A trading name exists with that currency name!<br /> Please choose another currency name.";
							} else {
							
								//check if there is a subspace name that exists for that currency name.
								$subspace_name = ($userSpace['space_name'] == '') ? $currencyName : $currencyName . "." . $userSpace['space_name'];
								$subspace_check_q = mysqli_query($db , $test = "SELECT * FROM spaces s WHERE s.space_name='" . $subspace_name . "'") or die($test . mysqli_error($db)); 
								if ( $subspace = mysqli_fetch_array( $subspace_check_q ) ) {
									$error = "A subspace exists with that currency name!<br /> Please choose another currency name.";
								} else {
									//All conditions have been met insert the currency
									mysqli_query($db , $test = "INSERT INTO currencies (currency, currency_steward) VALUES ('$currency_name', '".$this->user['id']."')" ) or die( $test . mysqli_error( $db ) );
									$currencyID = mysqli_insert_id($db);
								}
							}
						}
					} else {
						$error = "Currency name has to be less than 16 characters";
					}
				} else {
					$error = "Dots or spaces are not allowed in currency names!<br /> Please choose another currency name.";
				}
			}

		} else {
			//should probably never get this error because by default at lease one trading name domain is selected.
			$error = "You must select a trading name domain"; //user_space
		}
			
		$result_array = array();
		if($error != ''){
			$result_array = array("error"=>$error);
		} else if($uacID){
			$result_array = array("uacID"=>intval($uacID));
		} else {
			$result_array = array("currencyID"=>intval($currencyID));
		}
			
		$result = new Response(200, $result_array);
	
		return $result;
	}

	/**
	 * Condition method to turn output into JSON.
	 *
	 * This condition sets a before and an after filter for the request and response. The
	 * before filter decodes the request body if the request content type is JSON, while the
	 * after filter encodes the response body into JSON.
	 */
	protected function json()
	{
		$this->before(function ($request) {
			if ($request->contentType == "application/json") {
				$request->data = json_decode($request->data);
			}
		});
		$this->after(function ($response) {
			$response->contentType = "application/json";
			if (isset($_GET['jsonp'])) {
				$response->body = $_GET['jsonp'].'('.json_encode($response->body).');';
			} else {
				$response->body = json_encode($response->body);
			}
		});
	}

}



