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
 * @uri /payments/paymentData
 */
class paymentData extends Resource
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
	 * The optional :accountID parameter in the URL available as the first parameter to the method
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
	public function sayPaymentsData()
	{
		
		
			require("rest_connect.php");
				
				
			/*
			 Query parameters:
				destination: Payment receiver. May be one of the following strings:
				MEMBER: Searches transfer types to other members. This is the default.
				SYSTEM: Searches transfer types to system accounts.
				toMemberId: Internal identifier of the destination member. Either this or toMemberPrincipal are required if destination is MEMBER.
				toMemberPrincipal: Principal (username, custom field, email, etc, as configured on the rest channel) of the destination member. Either this or toMemberId are required if destination is MEMBER.
				fromAccountId: Returns transfer types only from the specified account. Optional.
				currencyId: Returns transfer types only with the specified currency, getting the currency by id. Optional.
				currencySymbol: Returns transfer types only with the specified currency, getting the currency by symbol. Optional.
			*/
			$destination = 'MEMBER';
			$toMemberId = 0;
			$exculdeToMemberId_q = '';
			$fromAccountId = 0;
			$fromAccount_q ='';
			$currencyId = 0;
			$currency_q = '';

				
			//$requestData = $this->request->data;
			if(isset($_GET)){
				if( isset($_GET['destination']) ){
					$destination = $_GET['destination'];
				}
				if(isset($_GET['toMemberId'])){
					$toMemberId = intval(mysqli_real_escape_string($db, $_GET['toMemberId']));
					$exculdeToMemberId_q = " AND uac.id!='$toMemberId' ";
				}
				if(isset($_GET['fromAccountId'])){
					$fromAccountId = intval(mysqli_real_escape_string($db, $_GET['fromAccountId']));
					$fromAccount_q = " AND uac.id='$fromAccountId' ";
				}
				if(isset($_GET['currencyId'])){
					$currencyId = intval(mysqli_real_escape_string($db, $_GET['currencyId']));
					$currency_q = " AND uac.currency_id = '$currencyId'";
				}

			}
			
			if($destination=='MEMBER'){
				

				$currency_id = 1;
				
				$transfer_types_array = array();
				$transfer_types_q = mysqli_query($db, $test = "SELECT *, uac.id user_account_currencies_id  FROM user_account_currencies uac, user_spaces us, currencies c WHERE uac.user_space_id=us.id AND uac.currency_id=c.id AND us.user_id='".$this->user['id']."' $currency_q $fromAccount_q $exculdeToMemberId_q ORDER BY uac.id ASC") or die($test . mysqli_error($db));
				while($transfer_types = mysqli_fetch_array($transfer_types_q)){
					
					$toMemberId_q = mysqli_query($db, $test = "SELECT *, uac.id user_account_currencies_id  FROM users u, user_account_currencies uac, user_spaces us WHERE uac.currency_id='".$transfer_types['currency_id']."' AND uac.user_space_id=us.id AND us.user_id=u.id AND uac.id='".mysqli_real_escape_string($db,$toMemberId)."' ORDER BY uac.id ASC") or die($test . mysqli_error($db));
					if($toMember = mysqli_fetch_array($toMemberId_q)){
					
						array_push($transfer_types_array, array("id"=>$transfer_types['user_account_currencies_id'],
															 "name"=>$transfer_types['trading_name'] . " " . $transfer_types['currency']." trade",
															 "from"=>array("id"=>$transfer_types['user_account_currencies_id'],
															 				"name"=>$transfer_types['trading_name'],
															 				"currency"=>array("id"=>$transfer_types['currency_id'],
																							   "symbol"=>$transfer_types['currency'],
																								"name"=>$transfer_types['currency'])
																		    ),
															 "to"=>array("id"=>$toMember['user_account_currencies_id'],
															 			  "name"=>$toMember['trading_name'],
															 			  "currency"=> array("id"=>$transfer_types['currency_id'],
																							   "symbol"=>$transfer_types['currency'],
																								"name"=>$transfer_types['currency']))));
						$currency_id = $transfer_types['currency_id'];
					}
				}
					
				$accounts_array = array();
				$accounts_q = mysqli_query($db,$test = "SELECT *, uac.id user_account_currencies_id FROM user_account_currencies uac, user_spaces us, currencies c  WHERE uac.currency_id=c.id AND uac.user_space_id=us.id AND us.user_id='".$this->user['id']."'") or die($test . mysqli_error($db));
				while($accounts = mysqli_fetch_array($accounts_q)){
	
					$balance = 0.000000;
					$balance_decimal = 0.00;
					$user_journal_q = @mysqli_query($db,$test = "SELECT * FROM user_journal WHERE currency='".$accounts['currency']."' AND trading_account='".$accounts['trading_name']."' AND user_id='".$this->user['id']."' ORDER BY tid DESC");
					if($user_journal = @mysqli_fetch_array($user_journal_q)){
						$balance = floatval($user_journal['balance']);
						$balance_decimal = number_format($balance,2);
					}
		
					$default = false;
					if($accounts['currency_id']==1)
						$default = true;
					$accounts_array[$accounts['user_account_currencies_id']] = array( "balance" => number_format($balance,6),
							"formattedBalance" => "$balance_decimal ".$accounts['currency'],
							"availableBalance" => number_format(0.000000,6),
							"formattedAvailableBalance" => "0.00 ".$accounts['currency'],
							"reservedAmount" => number_format(0.000000,6),
							"formattedReservedAmount" => "0.00 ".$accounts['currency'],
							"creditLimit" => number_format(0.000000,6),
							"formattedCreditLimit" => "0.00 ".$accounts['currency']);
	
					$default = false;
					if($accounts['currency_id']==1)
						$default = true;
	
	
	
				}
				
				$toMemberId_q = mysqli_query($db, $test = "SELECT *, uac.id user_account_currencies_id FROM users u, user_spaces us, user_account_currencies uac WHERE uac.currency_id='".$currency_id."' AND uac.user_space_id=us.id AND us.user_id=u.id AND uac.id='".mysqli_real_escape_string($db,$toMemberId)."'") or die($test . mysqli_error($db));
				$toMember = mysqli_fetch_array($toMemberId_q);

				
				$result_array = array("transferTypes" => $transfer_types_array,
									  "accountsStatus" => $accounts_array,
									  "toMember"=>array("id"=>$toMember['user_account_currencies_id'],
									  					 "name"=>$toMember['trading_name'],
									  					 "username"=>$toMember['user_name'],
									  					 "email"=>$toMember['email']));
				
				$result = new Response(200, $result_array);
			} else {
				throw new Tonic\NotAcceptableException;
			}
			
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


/**
 * The @uri annotation routes requests that match that URL to this resource. Multiple
 * annotations allow this resource to match multiple URLs.
 *
 * @uri /payments/memberPayment
 */
class memberPayment extends Resource
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
	 * The optional :accountID parameter in the URL available as the first parameter to the method
	 * or as a property of the resource as $this->name.
	 *
	 * Method can return a string response, an HTTP status code, an array of status code and
	 * response body, or a full Tonic\Response object.
	 *
	 * @method POST
     * @accepts application/json
	 * @provides application/json
	 * @json
	 * @return Tonic\Response
	 */
	public function sayMemberPayment()
	{


		require("rest_connect.php");


		/*
			Request body: Object with the following properties:
			transactionPassword: The transaction password, which might be needed to complete the payment. Whether this is needed depends on the group settings (if the member group uses transaction password) and the REST channel configuration, where only used if credentials is 'Default' (login password + transaction password)
			toMemberId: Internal identifier for the destination member. Either this or toMemberPrincipal are required.
			toMemberPrincipal: Principal (username, e-mail, custom field, etc, as configured on the rest channel) for the destination member. Either this or toMemberId are required.
			amount: Numerical amount for the payment. Required.
			transferTypeId: Internal identifier for payment transfer type. Optional.
			currencyId: Internal identifier for payment currency. Optional. If passing in a currency, use either currencyId or currencySymbol.
			currencySymbol: The symbol for the payment currency. Optional. If passing in a currency, use either currencyId or currencySymbol.
			installments: Number of installments for this payment. Optional. If empty, will be assumed 1, and processed directly. The interval between installments is one month.
			firstInstallmentDate: ISO 8601 formatted date for the first installment. Optional. If using multiple installments and the first date was not informed, the first installment will be immediately processed.
			description: Payment description. Optional. Defaults to the transfer type description.
			customValues: An array containing objects, each describing a custom field value, with the following properties:
			internalName: The custom field internal name
			fieldId: The custom field id
			possibleValueId: The custom field possible value id
			value: The custom field value
			hidden: A boolean flag indicating whether the field value is hidden
			The field of a custom field value can be identified passing the internal name (internalName) or field id (fieldId). If the value of a custom field value is an enumerated value it is sufficient to pass the possible value id (possibleValueId), or the enumerated string value in the value parameter.
		*/

		$toMemberId = 0;
		$amount = 0;
		$transferTypeId = 1;
		$description = "";

		$requestData = $this->request->data;
		
		if(isset($requestData->toMemberId)){
			$toMemberId = intval(mysqli_real_escape_string($db, $requestData->toMemberId));
		} 
		if(isset($requestData->amount)){
			$amount = floatval(mysqli_real_escape_string($db, $requestData->amount));
		}
		if(isset($requestData->transferTypeId)){
			$transferTypeId = intval(mysqli_real_escape_string($db, $requestData->transferTypeId));
		}
		if(isset($requestData->description)){
			$description = mysqli_real_escape_string($db, $requestData->description);
		}
		
		

			
		if($toMemberId > 0){

			$currency = 'cc'; //default currency

			$transfer_types_array = array();
			$transfer_types_q = mysqli_query($db, $test = "SELECT *, uac.id user_account_currencies_id  FROM user_account_currencies uac, user_spaces us, currencies c WHERE uac.id='".$transferTypeId."' AND uac.user_space_id=us.id AND uac.currency_id=c.id AND us.user_id='".$this->user['id']."'") or die($test . mysqli_error($db));
			if($transfer_types = mysqli_fetch_array($transfer_types_q)){
					
				$toMemberId_q = mysqli_query($db, $test = "SELECT *, uac.id user_account_currencies_id  FROM users u, user_account_currencies uac, user_spaces us WHERE uac.currency_id='".$transfer_types['currency_id']."' AND uac.user_space_id=us.id AND us.user_id=u.id AND uac.id='".mysqli_real_escape_string($db,$toMemberId)."'") or die($test . mysqli_error($db));
				if($toMember = mysqli_fetch_array($toMemberId_q)){
					
					$transfer_types_array = array("id"=>$transfer_types['user_account_currencies_id'],
						"name"=>$transfer_types['trading_name'] . " " . $transfer_types['currency'] . " trade",
						"from"=>array("id"=>$transfer_types['user_account_currencies_id'],
							"name"=>$transfer_types['trading_name'],
							"currency"=>array("id"=>$transfer_types['currency_id'],
								"symbol"=>$transfer_types['currency'],
								"name"=>$transfer_types['currency'])),
						"to"=>array("id"=>$toMember['user_account_currencies_id'],
							"name"=>$toMember['trading_name'],
							"currency"=> array("id"=>$transfer_types['currency_id'],
								"symbol"=>$transfer_types['currency'],
								"name"=>$transfer_types['currency'])));
					$currency = $transfer_types['currency'];
				}
			} else {
				//return error
			}
				
			
			$this_user_q = mysqli_query($db, $test = "SELECT *, uac.id user_account_currencies_id FROM users u, user_spaces us, user_account_currencies uac WHERE uac.user_space_id=us.id AND us.user_id=u.id AND u.id='".$this->user['id']."' ORDER BY uac.id ASC") or die($test . mysqli_error($db));
			$this_user = mysqli_fetch_array($this_user_q);
			

			$toMemberId_q = mysqli_query($db, $test = "SELECT *, uac.id user_account_currencies_id FROM users u, user_spaces us, user_account_currencies uac WHERE uac.user_space_id=us.id AND us.user_id=u.id AND uac.id='".mysqli_real_escape_string($db,$toMemberId)."' ORDER BY uac.id ASC") or die($test . mysqli_error($db));
			$toMember = mysqli_fetch_array($toMemberId_q);
			
			


			$result_array = array("wouldRequireAuthorization" => false,
					"from"=>array("id"=>$this_user['user_account_currencies_id'],
							"name"=>$this_user['trading_name'],
							"username"=>$this->user['user_name'],
							"email"=>$this->user['email']),
					"to"=>array("id"=>$toMember['user_account_currencies_id'],
							"name"=>$toMember['trading_name'],
							"username"=>$toMember['user_name'],
							"email"=>$toMember['email']),
					"finalAmount"=>number_format($amount,2),
					"formattedFinalAmount"=>number_format($amount,2)." ".$currency,
					"transferType" => $transfer_types_array);
					
					

			$result = new Response(200, $result_array);
		} else {
			throw new Tonic\NotAcceptableException;
		}
			
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



/**
 * The @uri annotation routes requests that match that URL to this resource. Multiple
 * annotations allow this resource to match multiple URLs.
 *
 * @uri /payments/confirmMemberPayment
 */
class confirmMemberPayment extends Resource
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
	 * The optional :accountID parameter in the URL available as the first parameter to the method
	 * or as a property of the resource as $this->name.
	 *
	 * Method can return a string response, an HTTP status code, an array of status code and
	 * response body, or a full Tonic\Response object.
	 *
	 * @method POST
	 * @accepts application/json
	 * @provides application/json
	 * @json
	 * @return Tonic\Response
	 */
	public function sayConfirmMemberPayment()
	{


		require("rest_connect.php");


		/*
			Request body: Object with the following properties:
		transactionPassword: The transaction password, which might be needed to complete the payment. Whether this is needed depends on the group settings (if the member group uses transaction password) and the REST channel configuration, where only used if credentials is 'Default' (login password + transaction password)
		toMemberId: Internal identifier for the destination member. Either this or toMemberPrincipal are required.
		toMemberPrincipal: Principal (username, e-mail, custom field, etc, as configured on the rest channel) for the destination member. Either this or toMemberId are required.
		amount: Numerical amount for the payment. Required.
		transferTypeId: Internal identifier for payment transfer type. Optional.
		currencyId: Internal identifier for payment currency. Optional. If passing in a currency, use either currencyId or currencySymbol.
		currencySymbol: The symbol for the payment currency. Optional. If passing in a currency, use either currencyId or currencySymbol.
		installments: Number of installments for this payment. Optional. If empty, will be assumed 1, and processed directly. The interval between installments is one month.
		firstInstallmentDate: ISO 8601 formatted date for the first installment. Optional. If using multiple installments and the first date was not informed, the first installment will be immediately processed.
		description: Payment description. Optional. Defaults to the transfer type description.
		customValues: An array containing objects, each describing a custom field value, with the following properties:
		internalName: The custom field internal name
		fieldId: The custom field id
		possibleValueId: The custom field possible value id
		value: The custom field value
		hidden: A boolean flag indicating whether the field value is hidden
		The field of a custom field value can be identified passing the internal name (internalName) or field id (fieldId). If the value of a custom field value is an enumerated value it is sufficient to pass the possible value id (possibleValueId), or the enumerated string value in the value parameter.
		*/

		$toMemberId = 0;
		$amount = 0;
		$transferTypeId = 1;
		$description = "";

		$requestData = $this->request->data;

		if(isset($requestData->toMemberId)){
			$toMemberId = intval(mysqli_real_escape_string($db, $requestData->toMemberId));
		}
		if(isset($requestData->amount)){
			$amount = floatval(mysqli_real_escape_string($db, $requestData->amount));
		}
		if(isset($requestData->transferTypeId)){
			$transferTypeId = intval(mysqli_real_escape_string($db, $requestData->transferTypeId));
		}
		if(isset($requestData->description)){
			$description = mysqli_real_escape_string($db,$requestData->description);
		}


			
		if($toMemberId > 0){
			
			
			$with_account = '';
			
			$toMemberId_q = mysqli_query($db, $test = "SELECT *, uac.id user_account_currencies_id FROM users u, user_spaces us, user_account_currencies uac WHERE uac.user_space_id=us.id AND us.user_id=u.id AND uac.id='".mysqli_real_escape_string($db,$toMemberId)."' ORDER BY uac.id ASC") or die($test . mysqli_error($db));
			if($toMember = mysqli_fetch_array($toMemberId_q)){
				$with_account = $toMember['trading_name'];
			} else {
				throw new Tonic\NotAcceptableException;
			}
			
			$trading_account = '';
			
			

			$currency = 'cc'; //default currency

			$transfer_types_array = array();
			$transfer_types_q = mysqli_query($db, $test = "SELECT *, uac.id user_account_currencies_id  FROM user_account_currencies uac, user_spaces us, currencies c WHERE uac.id='".$transferTypeId."' AND uac.user_space_id=us.id AND uac.currency_id=c.id AND us.user_id='".$this->user['id']."'") or die($test . mysqli_error($db));
			if($transfer_types = mysqli_fetch_array($transfer_types_q)){
					
				$currency = $transfer_types['currency'];
				$trading_account = $transfer_types['trading_name'];
				
			} else {
				//return error
				throw new Tonic\NotAcceptableException;
			}

				
			$this_user_q = mysqli_query($db, $test = "SELECT *, uac.id user_account_currencies_id FROM users u, user_spaces us, user_account_currencies uac WHERE uac.user_space_id=us.id AND us.user_id=u.id AND u.id='".$this->user['id']."' AND uac.id='".$transferTypeId."' ORDER BY uac.id ASC") or die($test . mysqli_error($db));
			$this_user = mysqli_fetch_array($this_user_q);
			
			$this_user_balance_q = mysqli_query($db, $test = "SELECT * FROM user_journal WHERE user_id='".$this->user['id']."' AND currency='".$currency."' AND trading_account='".$this_user['trading_name']."' ORDER by id DESC");
			$this_user_balance = mysqli_fetch_array($this_user_balance_q);
			
			$to_member_balance_q = mysqli_query($db, $test = "SELECT * FROM user_journal WHERE user_id='".$toMember['user_id']."' AND currency='".$currency."' AND trading_account='".$toMember['trading_name']."' ORDER by id DESC");
			$to_member_balance = mysqli_fetch_array($to_member_balance_q);
			
			$transaction_time = strtotime("now");
			$transaction_date = date("Y-m-d H:i:s",$transaction_time);
			//this users transaction record
			mysqli_query($db,$test = "INSERT INTO user_journal (user_id, tid, created, description, trading_account, with_account, currency, amount, balance, trading, flags) VALUES ".
															  "('".$this->user['id']."','$transaction_time','$transaction_date','$description','$trading_account','$with_account','$currency','".-1*$amount."','".($this_user_balance['balance']-$amount)."', '".($this_user_balance['trading']+$amount)."','m')") or die($test.mysqli_error($db));
			$transactionID = mysqli_insert_id($db);
			//to member transaction record
			//note that the member names are reversed.
			mysqli_query($db,$test = "INSERT INTO user_journal (user_id, tid, created, description, trading_account, with_account, currency, amount, balance, trading, flags) VALUES ".
					"('".$toMember['user_id']."','$transaction_time','$transaction_date','$description','$with_account','$trading_account','$currency','$amount','".($to_member_balance['balance']+$amount)."','".($to_member_balance['trading']+$amount)."','m')") or die($test.mysqli_error($db));
				
			$result_array = array("id"=>$transactionID,
								  "pending"=>false);

			$result = new Response(200, $result_array);
		} else {
			throw new Tonic\NotAcceptableException;
		}
			
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

