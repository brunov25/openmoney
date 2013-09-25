<?php

namespace openmoney;

use Tonic;
use Tonic\Resource,
Tonic\Response,
Tonic\ConditionException;




/**
 * The @uri annotation routes requests that match that URL to this resource. Multiple
 * annotations allow this resource to match multiple URLs.
 *
 * @uri /members
 */
class members extends Resource
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
	 * @return Tonic\Response
	 */
	public function sayMembers()
	{
		

		

		/*
		 * 
		 * Query parameters:
			currentPage: Number indicating the current result page, starting with 0 (the default)
			pageSize: Amount of results returned. 10 by default, 100 max.
			keywords: String used to match name, username, e-mail and custom fields in a full-text search.
			withImagesOnly: If set to true, returns only members which have at least one image. Accepts true / false. False by default.
			showCustomFields: Indicates whether the results should include custom field values. Accepts true / false. False by default.
			showImages: Indicates whether the results should include images. Accepts true / false. False by default.
			excludeLoggedIn: Indicates whether to exclude the logged user from results. Accepts true / false. False by default.
			customValue.<fieldInternalName>: Value for a custom field filter for a given field by internal name. Only works for custom fields marked for search. In case the custom field has an enumerated type, the id of a possible value should be entered here.
		 */
		
		require("rest_connect.php");
	
		$currentPage = 0;
		$pageSize = 10;
		$keywords = '';
		$withImagesOnly = false;
		$showCustomFields = false;
		$showImages = false;
		$excludeLoggedIn = true;
		
		if(isset($_GET['currentPage']))
			$currentPage = intval(mysqli_real_escape_string($db, $_GET['currentPage']));
		if(isset($_GET['pageSize']))
			$pageSize = intval(mysqli_real_escape_string($db, $_GET['pageSize']));
		if(isset($_GET['keywords']))
			$keywords = mysqli_real_escape_string($db, $_GET['keywords']);
		if(isset($_GET['withImagesOnly']))
			$withImagesOnly = $this->boolval(mysqli_real_escape_string($db,$_GET['withImagesOnly']));
		if(isset($_GET['showCustomFields']))
			$showCustomFields = $this->boolval(mysqli_real_escape_string($db,$_GET['showCustomFields']));
		if(isset($_GET['showImages']))
			$showImages = $this->boolval(mysqli_real_escape_string($db,$_GET['showImages']));
		//not used anymore
		if(isset($_GET['excludeLoggedIn']))
			$excludeLoggedIn = $this->boolval(mysqli_real_escape_string($db,$_GET['excludeLoggedIn']));
		
	
	
		$startEntry = $currentPage * $pageSize;
		
		
		
		//get My currencies and Accounts
		$myCurrenciesArray = array();
		$myTradingNamesArray = array();
		$myAccounts_q = mysqli_query($db, $test = "SELECT * FROM user_account_currencies uac, user_spaces us WHERE us.id=uac.user_space_id AND us.user_id='".$this->user['id']."'") or die($test.mysqli_error($db));
		while($myAccounts = mysqli_fetch_array($myAccounts_q)){
			array_push($myCurrenciesArray, $myAccounts['currency_id']);
			array_push($myTradingNamesArray, $myAccounts['trading_name']);
		}
		
		//make an array of possible trading accounts
		$tradingNamesArray = array();
		$keywords_q = '';
		if($keywords) {
			array_push($tradingNamesArray,$keywords);
		}
		$trading_name_history_q = mysqli_query($db,$test = "SELECT * FROM user_journal WHERE user_id='".$this->user['id']."' AND (trading_account like '%$keywords%' OR with_account like '%$keywords%')");
		while($trading_name_history = mysqli_fetch_array($trading_name_history_q)){
			array_push($tradingNamesArray,$trading_name_history['trading_account']);
			array_push($tradingNamesArray,$trading_name_history['with_account']);
		}
		
		//If I only have one trading account in that currency I cannot trade with that account.
		$exclude_q = mysqli_query($db, $test = "SELECT *, uac.id user_account_currencies_id FROM user_account_currencies uac, user_spaces us, currencies c WHERE c.id=uac.currency_id AND us.id=uac.user_space_id AND us.user_id='".$this->user['id']."' GROUP BY c.id HAVING count(*)=1") or die($test.mysqli_error($db));
		while($exclude = mysqli_fetch_array($exclude_q)){
			//iterate through trading names
			foreach($tradingNamesArray as $key => $trading_name){
				//if the trading name exists remove it.
				if($trading_name==$exclude['trading_name']){
					unset($tradingNamesArray[$key]);
				}
			}
		}
		
		//build a query of all possible trading accounts
		if(!empty($tradingNamesArray)){
			foreach($tradingNamesArray as $trading_name){
				//if(!in_array($trading_name,$myTradingNamesArray)){
					$keywords_q .= " OR uac.trading_name='$trading_name'";
				//}
			}
			if(strlen($keywords_q) > 4){
				$keywords_q = substr($keywords_q,4); //remove begining OR
				$keywords_q = " AND ($keywords_q) "; // enclose in Parentheses and add AND
			}
		}
		
		

		
		//Seach through trading accounts on this system.
		$totalCount = 0;
		$members_array = array();
		$members_q = mysqli_query($db,$test = "SELECT *, uac.id user_account_currencies_id FROM user_account_currencies uac, user_spaces us, currencies c, users u  WHERE us.user_id=u.id AND uac.currency_id=c.id AND uac.user_space_id=us.id $keywords_q LIMIT $startEntry, $pageSize") or die($test . mysqli_error($db));
		while($members = mysqli_fetch_array($members_q)){
			//if the member has a currency I have
			if(in_array($members['currency_id'],$myCurrenciesArray)){
				$totalCount++;
				array_push($members_array,array("id"=>$members['user_account_currencies_id'],
												"name"=>$members['trading_name'] . " " . $members['currency'],
												"username"=>$members['user_name'],
												"email"=>$members['email']));
			}

		}
		$result_array = array("currentPage" => $currentPage,
							  "pageSize" => $pageSize,
							  "totalCount" => $totalCount,
							  "elements" => $members_array);
		
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
	

	/** Checks a variable to see if it should be considered a boolean true or false.
	 *     Also takes into account some text-based representations of true of false,
	 *     such as 'false','N','yes','on','off', etc.
	 * @author Samuel Levy <sam+nospam@samuellevy.com>
	 * @param mixed $in The variable to check
	 * @param bool $strict If set to false, consider everything that is not false to
	 *                     be true.
	 * @return bool The boolean equivalent or null
	 */
     protected function boolval($in, $strict=false) {
		$out = null;
		// if not strict, we only have to check if something is false
		if (in_array($in,array('false', 'False', 'FALSE', 'no', 'No', 'n', 'N', '0', 'off',
				'Off', 'OFF', false, 0, null), true)) {
				$out = false;
		} else if ($strict) {
			// if strict, check the equivalent true values
			if (in_array($in,array('true', 'True', 'TRUE', 'yes', 'Yes', 'y', 'Y', '1',
					'on', 'On', 'ON', true, 1), true)) {
					$out = true;
			}
		} else {
			// not strict? let the regular php bool check figure it out (will
			//     largely default to true)
			$out = ($in?true:false);
		}
		return $out;
	}

}




/**
 * The @uri annotation routes requests that match that URL to this resource. Multiple
 * annotations allow this resource to match multiple URLs.
 *
 * @uri /members/memberData/:memberId
 */
class memberData extends Resource
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
	 * @param $memberId
	 * @provides application/json
	 * @json
	 * @return Tonic\Response
	 */
	public function sayMemberData($memberId)
	{
	
	
	
		require("rest_connect.php");
	
	
		$excludeLoggedIn = true;
	
		$exclude_q = '';
		if($excludeLoggedIn)
			$exclude_q = " AND us.user_id!='".$this->user['id']."' ";
	
		$totalCount = 0;
		$members_array = array();
		$members_q = mysqli_query($db,$test = "SELECT *, uac.id user_account_currencies_id FROM user_account_currencies uac, user_spaces us, currencies c, users u  WHERE us.user_id=u.id AND uac.currency_id=c.id AND uac.user_space_id=us.id $exclude_q ") or die($test . mysqli_error($db));
		if($members = mysqli_fetch_array($members_q)){
			$totalCount++;
			$members_array = array("id"=>$members['user_account_currencies_id'],
					"name"=>$members['trading_name'],
					"username"=>$members['user_name'],
					"email"=>$members['email']);
	
		}
		
		$result_array = array("member" => $members_array,
							   "canAddMemberAsContact" => false);
	
	
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
	