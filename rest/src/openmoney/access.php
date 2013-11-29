<?php

namespace openmoney;

use Tonic;
use Tonic\Resource, Tonic\Response, Tonic\ConditionException;

/**
 * The obligitory access World example
 *
 * The @uri annotation routes requests that match that URL to this resource. Multiple
 * annotations allow this resource to match multiple URLs.
 *
 * @uri /access/initialData
 */
class access extends Resource {
	private $user;
	private $username;
	
	/**
	 * The setup() method is called when the resource is executed.
	 * We don't do this check
	 * within the resource constructor as we can't cleanly throw an exception from within
	 * an object constructor.
	 */
	function setup() {
		require ("rest_connect.php");
		require ("../password.php");
		if (isset ( $_SERVER ['PHP_AUTH_USER'] ) && isset ( $_SERVER ['PHP_AUTH_PW'] )) {
			
			// echo "Attempting to authenticate user.\n";
			$users_q = mysqli_query ( $db, "SELECT * FROM users WHERE user_name='" . mysqli_real_escape_string ( $db, $_SERVER ['PHP_AUTH_USER'] ) . "'" );
			$users = mysqli_fetch_array ( $users_q );
			if (password_verify ( $_SERVER ['PHP_AUTH_PW'], $users ['password2'] ) or (password_verify ( $_SERVER ['PHP_AUTH_PW'], $users ['password'] ))) {
				// echo "verified\n";
				$this->user = $users;
				$this->username = $users ['user_name'];
			} else {
				throw new Tonic\UnauthorizedException ();
			}
		} else {
			throw new Tonic\UnauthorizedException ();
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
	 *         @provides application/json
	 *         @json
	 *         @cache 0
	 * @return Tonic\Response
	 */
	public function sayaccess() {
		require ("rest_connect.php");
		$accounts_array = array ();
		$default_count = 0;
		$accounts_q = mysqli_query ( $db, $test = "SELECT *, uac.id user_account_currencies_id FROM user_account_currencies uac, user_spaces us, currencies c  WHERE uac.currency_id=c.id AND uac.user_space_id=us.id AND us.user_id='" . $this->user ['id'] . "' ORDER BY uac.id ASC" ) or die ( $test . mysqli_error ( $db ) );
		while ( $accounts = mysqli_fetch_array ( $accounts_q ) ) {
			$default = false;
			if (($accounts ['currency_id'] == 1) && ($default_count == 0)) {
				$default_count ++;
				$default = true;
			}
			
			array_push ( $accounts_array, array (
					"id" => intval ( $accounts ['user_account_currencies_id'] ),
					"type" => array (
							"id" => intval ( $accounts ['user_account_currencies_id'] ),
							"name" => $accounts ['trading_name'],
							"currency" => array (
									"id" => intval ( $accounts ['currency_id'] ),
									"symbol" => $accounts ['currency'],
									"name" => $accounts ['currency'] 
							) 
					),
					"default" => $default 
			) );
		}
		$result = new Response ( 200, array (
				'profile' => array (
						'id' => intval ( $this->user ['id'] ),
						'name' => $this->user ['fname'] . " " . $this->user ['lname'],
						'firstname' => $this->user ['fname'],
						'lastname' => $this->user ['lname'],
						'username' => $this->username,
						'email' => $this->user ['email'],
						'customValues' => array (
								array (
										"internalName" => "gender",
										"fieldId" => 2,
										"displayName" => "Gender",
										"value" => "Male",
										"possibleValueId" => 1 
								),
								array (
										"internalName" => "address",
										"fieldId" => 3,
										"displayName" => "Address",
										"value" => "1234 my Street" 
								),
								array (
										"internalName" => "postalCode",
										"fieldId" => 4,
										"displayName" => "Postal code",
										"value" => "V8N 0R2" 
								),
								array (
										"internalName" => "city",
										"fieldId" => 5,
										"displayName" => "City",
										"value" => "Victoria" 
								) 
						) 
				),
				'requireTransactionPassword' => false,
				'accounts' => $accounts_array,
				'canMakeMemberPayments' => true,
				'canMakeSystemPayments' => false,
				'decimalCount' => 2,
				'decimalSeparator' => "." 
		) );
		
		return $result;
	}
	
	/**
	 * Condition method to turn output into JSON.
	 *
	 * This condition sets a before and an after filter for the request and response. The
	 * before filter decodes the request body if the request content type is JSON, while the
	 * after filter encodes the response body into JSON.
	 */
	protected function json() {
		$this->before ( function ($request) {
			if ($request->contentType == "application/json") {
				$request->data = json_decode ( $request->data );
			}
		} );
		$this->after ( function ($response) {
			$response->contentType = "application/json";
			if (isset ( $_GET ['jsonp'] )) {
				$response->body = $_GET ['jsonp'] . '(' . json_encode ( $response->body ) . ');';
			} else {
				$response->body = json_encode ( $response->body );
			}
		} );
	}
}

/**
 * The @uri annotation routes requests that match that URL to this resource. Multiple
 * annotations allow this resource to match multiple URLs.
 *
 * @uri /access/register
 */
class accessRegister extends Resource {
	private $user;
	private $username;
	
	/**
	 * The setup() method is called when the resource is executed.
	 * We don't do this check
	 * within the resource constructor as we can't cleanly throw an exception from within
	 * an object constructor.
	 */
	function setup() {
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
	 * @method POST
	 *         @accepts application/json
	 *         @provides application/json
	 *         @json
	 *         @cache 0
	 * @return Tonic\Response
	 */
	public function sayaccessRegister() {
		require ("rest_connect.php");
		require ("../config.php");
		require ("../password.php");
		function email_letter($to, $from, $subject = 'no subject', $msg = 'no msg') {
			$headers = "From: $from\r\n";
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
			return mail ( $to, $subject, $msg, $headers );
		}
		
		$requestData = $this->request->data;
		
		$username = "";
		$email = "";
		$password = "";
		$password2 = "";
		
		$error = "";
		
		if (isset ( $requestData->username )) {
			$username = mysqli_real_escape_string ( $db, $requestData->username );
		} else {
			$error .= "Username is required!<br/>";
			return new Response ( 200, array (
					'errorDetails' => $error 
			) );
		}
		if (isset ( $requestData->email )) {
			$email = mysqli_real_escape_string ( $db, $requestData->email );
		} else {
			$error .= "Email is required!<br/>";
			return new Response ( 200, array (
					'errorDetails' => $error 
			) );
		}
		if (isset ( $requestData->password )) {
			$password = mysqli_real_escape_string ( $db, $requestData->password );
		} else {
			$error .= "Password is required!<br/>";
			return new Response ( 200, array (
					'errorDetails' => $error 
			) );
		}
		
		if (isset ( $requestData->password2 )) {
			$password2 = mysqli_real_escape_string ( $db, $requestData->password2 );
		}
		
		$allowInternational = false;
		if (defined ( 'PCRE_VERSION' )) {
			if (intval ( PCRE_VERSION ) >= 7) { // constant available since PHP 5.2.4
				$allowInternational = true;
			}
		}
		
		if (! $allowInternational) {
			$error .= "Your php version is too old!!! update php > 5.2.4<br/>";
			return new Response ( 200, array (
					'errorDetails' => $error 
			) );
		}
		
		$init_space = $CFG->default_space;
		
		$usernamePattern = "/^[\p{L}\p{N}_\-\.]+$/u"; // International Letters, Numbers, Underscores and hyphens only
		$usernamePatternNumberOfCharacters = "/^[\p{L}\p{N}_\-\.]{1,30}$/u"; // international Letters, Numbers, underscore and hyphen 3 to 30 characters
		if (preg_match ( $usernamePattern, $username )) {
			// valid username check number of chars
			if (preg_match ( $usernamePatternNumberOfCharacters, $username )) {
				// username has vaild characters
				
				$usernameCheck_q = mysqli_query ( $db, $test = "SELECT * FROM users WHERE user_name='$username'" ) or die ( $test . mysqli_error ( $db ) );
				if ($usernameCheck_q = mysqli_fetch_array ( $usernameCheck_q )) {
					$error .= "Username already exists! Please choose another Username.<br/>";
					return new Response ( 200, array (
							'errorDetails' => $error 
					) );
				}
				
				$usernameCheck_q = mysqli_query ( $db, $test = "SELECT * FROM currencies WHERE currency='$username'" ) or die ( $test . mysqli_error ( $db ) );
				if ($usernameCheck_q = mysqli_fetch_array ( $usernameCheck_q )) {
					$error .= "Username exists as currency! Please choose another Username.<br/>";
					return new Response ( 200, array (
							'errorDetails' => $error 
					) );
				}
				
				$usernameCheck_q = mysqli_query ( $db, $test = "SELECT * FROM user_account_currencies WHERE trading_name='$username'" ) or die ( $test . mysqli_error ( $db ) );
				if ($usernameCheck_q = mysqli_fetch_array ( $usernameCheck_q )) {
					$error .= "Username exists as trading name! Please choose another Username.<br/>";
					return new Response ( 200, array (
							'errorDetails' => $error 
					) );
				}
				
				$usernameCheck_q = mysqli_query ( $db, $test = "SELECT * FROM spaces WHERE space_name='$username'" ) or die ( $test . mysqli_error ( $db ) );
				if ($usernameCheck_q = mysqli_fetch_array ( $usernameCheck_q )) {
					$error .= "Username exists as space name! Please choose another Username.<br/>";
					return new Response ( 200, array (
							'errorDetails' => $error 
					) );
				}
				
				$dotPattern = "/^([\p{L}\p{N}_-]+\.)+([\p{L}\p{N}_-]+)$/u";
				if (preg_match ( $dotPattern, $username, $matches )) {
					$user_name = $matches [0]; // contains dot
					$match = '';
					for($i = count ( $matches ) - 1; $i > 1; $i --) {
						// concatenate match to beginning of string.
						$match = $matches [$i] . $match;
						// do a space check to make sure it exists first.
						$usernameCheck_q = mysqli_query ( $db, $test = "SELECT * FROM spaces WHERE space_name='$match'" ) or die ( $test . mysqli_error ( $db ) );
						if (! ($usernameCheck = mysqli_fetch_array ( $usernameCheck_q ))) {
							$error .= "$match subspace does not exist!<br/> Please choose another Username.<br/>";
							return new Response ( 200, array (
									'errorDetails' => $error 
							) );
						}
					}
					$init_space = $match;
					// $error .= "pattern did match subspace! Username:".$user_name." Subspace:".$init_space." Please choose another Username.<br/>";
					// return new Response(200, array('errorDetails' => $error));
				} else {
					// $error .= "pattern didn't match subspace! Please choose another Username.<br/>";
					// return new Response(200, array('errorDetails' => $error));
				}
			} else {
				$error .= "Username has too many characters!<br/>";
				return new Response ( 200, array (
						'errorDetails' => $error 
				) );
			}
		} else {
			// error pattern does not match allowed characters
			$error .= "Username contains invalid characters!<br/>International Letters, Numbers, Underscores and hyphens only";
			return new Response ( 200, array (
					'errorDetails' => $error 
			) );
		}
		
		$emailPattern = "/^([\p{L}\p{N}\+_\.-]+)@([\p{N}\p{L}\.-]+)\.([\p{L}\.]{2,6})$/u";
		if (preg_match ( $emailPattern, $email )) {
			// valid email
			$emailCheck_q = mysqli_query ( $db, $test = "SELECT * FROM users WHERE email='$email'" ) or die ( $test . mysqli_error ( $db ) );
			if ($usernameCheck_q = mysqli_fetch_array ( $emailCheck_q )) {
				$error .= "Email already exists! Please choose another Email.<br/>";
				return new Response ( 200, array (
						'errorDetails' => $error 
				) );
			}
		} else {
			$error .= "Email address contains invalid characters!<br/>"; // International Letters, Numbers, Underscores, hyphens, plus and dots only
			return new Response ( 200, array (
					'errorDetails' => $error 
			) );
		}
		
		if (strlen ( $password ) > 0) {
			if ($password != $password2) {
				$error .= "Passwords do not match!<br/>";
				return new Response ( 200, array (
						'errorDetails' => $error 
				) );
			}
		} else {
			// password is too short
			$error .= "password is too short!<br/>";
			return new Response ( 200, array (
					'errorDetails' => $error 
			) );
		}
		$confirmed = false;
		
		if ($error == '') {
			// no error found insert user
			
			$password_hash = password_hash ( $password, PASSWORD_BCRYPT );
			
			$init_currency = $CFG->default_currency;
			$confirmed = ($CFG->site_type != 'Live') ? '1' : '0';
			// if (!$confirmed) { $password_hash = $password2_hash = ''; }
			$user_q = mysqli_query ( $db, $test = "INSERT INTO users (`user_name`, `email`, `password`, `password2`, `init_space`, `init_curr`, `confirmed`) VALUES ('$username', '$email', '$password_hash', '$password_hash', '$init_space', '$init_currency', '$confirmed')" ) or die ( $test . mysqli_error ( $db ) );
			$userID = mysqli_insert_id ( $db );
			
			if (!$confirmed){
				//check initial space exists
				$init_space_q = mysqli_query ( $db, $test = "SELECT * FROM spaces WHERE space_name='$init_space' ORDER BY id ASC" ) or die ( $test . mysqli_error ( $db ) );
				if (! ($initSpace = mysqli_fetch_array ( $init_space_q )) ) {
					$error .= "Could not find default space !!! Contact system administration!";
					// reverse inserts
					mysqli_query ( $db, $test = "DELETE FROM users WHERE id='$userID'" ) or die ( $test . mysqli_error ( $db ) );
					return new Response ( 200, array (
							'errorDetails' => $error
					) );
				}
				//check initial currency exists
				$user_currency_q = mysqli_query ( $db, $test = "SELECT * FROM currencies WHERE currency='$init_currency' ORDER BY id ASC" ) or die ( $test . mysqli_error ( $db ) );
				if (! ($userCurrency = mysqli_fetch_array ( $user_currency_q ))) {
					$error .= "Failed to find default user currency!!! Contact system administration!";
					// reverse inserts
					mysqli_query ( $db, $test = "DELETE FROM users WHERE id='$userID'" ) or die ( $test . mysqli_error ( $db ) );
					return new Response ( 200, array (
							'errorDetails' => $error
					) );
				}
				//username is checked for above
				
			} else {
				
				// make user a user of initial space
				$init_space_q = mysqli_query ( $db, $test = "SELECT * FROM spaces WHERE space_name='$init_space' ORDER BY id ASC" ) or die ( $test . mysqli_error ( $db ) );
				if ($initSpace = mysqli_fetch_array ( $init_space_q )) {
					
					$spaceID = $initSpace ['id'];
					$user_space_q = mysqli_query ( $db, $test = "INSERT INTO user_spaces (`user_id`,`space_id`,`class`) VALUES ('$userID', '$spaceID', 'user')" ) or die ( $test . mysqli_error ( $db ) );
					$userSpaceID = mysqli_insert_id ( $db );
					
					if ($userSpaceID > 0) {
						$user_currency_q = mysqli_query ( $db, $test = "SELECT * FROM currencies WHERE currency='$init_currency' ORDER BY id ASC" ) or die ( $test . mysqli_error ( $db ) );
						if ($userCurrency = mysqli_fetch_array ( $user_currency_q )) {
							$currencyID = $userCurrency ['id'];
							$user_account_currencies_q = mysqli_query ( $db, $test = "INSERT INTO user_account_currencies (`trading_name`, `user_space_id`, `currency_id`) VALUES ('$username','$userSpaceID','$currencyID') " ) or die ( $test . mysqli_error ( $db ) );
							$userAccountCurrenciesID = mysqli_insert_id ( $db );
							
							if ($userAccountCurrenciesID > 0) {
								// user account created
							} else {
								$error .= "Failed to insert trading name!!! Contact system administration!";
								// reverse inserts
								mysqli_query ( $db, $test = "DELETE FROM user_spaces WHERE id='$userSpaceID'" ) or die ( $test . mysqli_error ( $db ) );
								mysqli_query ( $db, $test = "DELETE FROM users WHERE id='$userID'" ) or die ( $test . mysqli_error ( $db ) );
								return new Response ( 200, array (
										'errorDetails' => $error 
								) );
							}
						} else {
							$error .= "Failed to find default user currency!!! Contact system administration!";
							// reverse inserts
							mysqli_query ( $db, $test = "DELETE FROM user_spaces WHERE id='$userSpaceID'" ) or die ( $test . mysqli_error ( $db ) );
							mysqli_query ( $db, $test = "DELETE FROM users WHERE id='$userID'" ) or die ( $test . mysqli_error ( $db ) );
							return new Response ( 200, array (
									'errorDetails' => $error 
							) );
						}
					} else {
						$error .= "Failed to insert user into default space!!! Contact system administration!";
						// reverse inserts
						mysqli_query ( $db, $test = "DELETE FROM users WHERE id='$userID'" ) or die ( $test . mysqli_error ( $db ) );
						return new Response ( 200, array (
								'errorDetails' => $error 
						) );
					}
				} else {
					$error .= "Could not find default space !!! Contact system administration!";
					// reverse inserts
					mysqli_query ( $db, $test = "DELETE FROM users WHERE id='$userID'" ) or die ( $test . mysqli_error ( $db ) );
					return new Response ( 200, array (
							'errorDetails' => $error 
					) );
				}
				
				if ($error == '') {
					// create personal space
					$created = date ( "Y-m-d H:i:s" );
					$space_create_q = mysqli_query ( $db, $test = "INSERT INTO spaces (`space_name`,`created`) VALUES ('$username', '$created') " ) or die ( $test . mysqli_error ( $db ) );
					$spaceID = mysqli_insert_id ( $db );
					
					if ($spaceID > 0) {
						
						// make user a steward of their space
						
						$user_space_q = mysqli_query ( $db, $test = "INSERT INTO user_spaces (`user_id`,`space_id`,`class`) VALUES ('$userID', '$spaceID', 'steward')" ) or die ( $test . mysqli_error ( $db ) );
						if ($userSpaceID = mysqli_insert_id ( $db )) {
						} else {
							$error .= "Could not make user a steward of personal space!!! Contact system administration!";
							// reverse inserts
							mysqli_query ( $db, $test = "DELETE FROM user_spaces WHERE id='$userSpaceID'" ) or die ( $test . mysqli_error ( $db ) );
							mysqli_query ( $db, $test = "DELETE FROM users WHERE id='$userID'" ) or die ( $test . mysqli_error ( $db ) );
							mysqli_query ( $db, $test = "DELETE FROM spaces WHERE id='$spaceID'" ) or die ( $test . mysqli_error ( $db ) );
							return new Response ( 200, array (
									'errorDetails' => $error 
							) );
						}
					} else {
						$error .= "Could not create personal space!!! Contact system administration!";
						// reverse inserts
						mysqli_query ( $db, $test = "DELETE FROM user_spaces WHERE id='$userSpaceID'" ) or die ( $test . mysqli_error ( $db ) );
						mysqli_query ( $db, $test = "DELETE FROM users WHERE id='$userID'" ) or die ( $test . mysqli_error ( $db ) );
						return new Response ( 200, array (
								'errorDetails' => $error 
						) );
					}
				}
			}
		}
		
		if ($error == '') {
			$address = $CFG->admin_email;
			$address2 = $CFG->maintainer;
			$confirmed = ($CFG->site_type == 'Live') ? "needs <a href={$CFG->url}/menu.php?confirm=1>confirmation</a>" : "was auto-confirmed";
			$msg = $email . " created an account $username on {$CFG->site_name} which $confirmed.<p>OpenMoney IT Team</p>";
			$subject = "{$CFG->site_name}: new account REQUESTED for $username ";
			email_letter ( $address, $CFG->system_email, $subject, $msg );
			email_letter ( $address2, $CFG->system_email, $subject, $msg );
		}
		
		if ($error != '') {
			$result = new Response ( 200, array (
					'errorDetails' => $error 
			) );
		} else {
			
			if ($CFG->site_type == 'Live') {
				$result = new Response ( 200, array (
						'registrationRequiresApproval' => true 
				) );
			} else {
				$accounts_array = array ();
				$default_count = 0;
				$accounts_q = mysqli_query ( $db, $test = "SELECT *, uac.id user_account_currencies_id FROM user_account_currencies uac, user_spaces us, currencies c  WHERE uac.currency_id=c.id AND uac.user_space_id=us.id AND us.user_id='" . $userID . "' ORDER BY uac.id ASC" ) or die ( $test . mysqli_error ( $db ) );
				while ( $accounts = mysqli_fetch_array ( $accounts_q ) ) {
					$default = false;
					if (($accounts ['currency_id'] == 1) && ($default_count == 0)) {
						$default_count ++;
						$default = true;
					}
					
					array_push ( $accounts_array, array (
							"id" => intval ( $accounts ['user_account_currencies_id'] ),
							"type" => array (
									"id" => intval ( $accounts ['user_account_currencies_id'] ),
									"name" => $accounts ['trading_name'],
									"currency" => array (
											"id" => intval ( $accounts ['currency_id'] ),
											"symbol" => $accounts ['currency'],
											"name" => $accounts ['currency'] 
									) 
							),
							"default" => $default 
					) );
				}
				$result = new Response ( 200, array (
						'profile' => array (
								'id' => intval ( $userID ),
								'name' => " ",
								'firstname' => "",
								'lastname' => "",
								'username' => $username,
								'email' => $email,
								'customValues' => array (
										array (
												"internalName" => "gender",
												"fieldId" => 2,
												"displayName" => "Gender",
												"value" => "Male",
												"possibleValueId" => 1 
										),
										array (
												"internalName" => "address",
												"fieldId" => 3,
												"displayName" => "Address",
												"value" => "1234 my Street" 
										),
										array (
												"internalName" => "postalCode",
												"fieldId" => 4,
												"displayName" => "Postal code",
												"value" => "V8N 0R2" 
										),
										array (
												"internalName" => "city",
												"fieldId" => 5,
												"displayName" => "City",
												"value" => "Victoria" 
										) 
								) 
						),
						'requireTransactionPassword' => false,
						'accounts' => $accounts_array,
						'canMakeMemberPayments' => true,
						'canMakeSystemPayments' => false,
						'decimalCount' => 2,
						'decimalSeparator' => ".",
						'registrationRequiresApproval' => false 
				) );
			}
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
	protected function json() {
		$this->before ( function ($request) {
			if ($request->contentType == "application/json") {
				$request->data = json_decode ( $request->data );
			}
		} );
		$this->after ( function ($response) {
			$response->contentType = "application/json";
			if (isset ( $_GET ['jsonp'] )) {
				$response->body = $_GET ['jsonp'] . '(' . json_encode ( $response->body ) . ');';
			} else {
				$response->body = json_encode ( $response->body );
			}
		} );
	}
}

/**
 * The @uri annotation routes requests that match that URL to this resource. Multiple
 * annotations allow this resource to match multiple URLs.
 *
 * @uri /access/updateProfile
 */
class accessUpdateProfile extends Resource {
	private $user;
	private $username;
	
	/**
	 * The setup() method is called when the resource is executed.
	 * We don't do this check
	 * within the resource constructor as we can't cleanly throw an exception from within
	 * an object constructor.
	 */
	function setup() {
		require ("rest_connect.php");
		require ("../password.php");
		if (isset ( $_SERVER ['PHP_AUTH_USER'] ) && isset ( $_SERVER ['PHP_AUTH_PW'] )) {
			
			// echo "Attempting to authenticate user.\n";
			$users_q = mysqli_query ( $db, "SELECT * FROM users WHERE user_name='" . mysqli_real_escape_string ( $db, $_SERVER ['PHP_AUTH_USER'] ) . "'" );
			$users = mysqli_fetch_array ( $users_q );
			if (password_verify ( $_SERVER ['PHP_AUTH_PW'], $users ['password2'] ) or (password_verify ( $_SERVER ['PHP_AUTH_PW'], $users ['password'] ))) {
				// echo "verified\n";
				$this->user = $users;
				$this->username = $users ['user_name'];
			} else {
				throw new Tonic\UnauthorizedException ();
			}
		} else {
			throw new Tonic\UnauthorizedException ();
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
	 * @method POST
	 *         @accepts application/json
	 *         @provides application/json
	 *         @json
	 *         @cache 0
	 * @return Tonic\Response
	 */
	public function sayaccessUpdateProfile() {
		require ("rest_connect.php");
		require ("../config.php");
		require ("../password.php");
		function email_letter($to, $from, $subject = 'no subject', $msg = 'no msg') {
			$headers = "From: $from\r\n";
			// $headers .= "To: $to\r\n";
			// $headers .= "Reply-To: $from\r\n";
			// $headers .= "Return-Path: $from\r\n";
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
			return mail ( $to, $subject, $msg, $headers );
		}
		
		$requestData = $this->request->data;
		
		$firstname = "";
		$lastname = "";
		$username = "";
		$email = "";
		$password = "";
		$password2 = "";
		
		$error = "";
		
		if (isset ( $requestData->firstname )) {
			$firstname = mysqli_real_escape_string ( $db, $requestData->firstname );
			
			$namePattern = "/^['-\p{L}\s]+$/u"; // international letters
			$namePatternNumberOfCharacters = "/^['-\p{L}\s]{2,32}$/u"; // 2-32 international letters
			if (preg_match ( $namePattern, $firstname )) {
				if (preg_match ( $namePatternNumberOfCharacters, $firstname )) {
					// first name is valid
				} else {
					$error .= "First name has not enough or too many characters!<br/>";
					return new Response ( 200, array (
							'errorDetails' => $error 
					) );
				}
			} else {
				$error .= "First name is only allowed lower case and upper case characters!<br/>";
				return new Response ( 200, array (
						'errorDetails' => $error 
				) );
			}
		}
		if (isset ( $requestData->lastname )) {
			$lastname = mysqli_real_escape_string ( $db, $requestData->lastname );
			
			$namePattern = "/^['-\p{L}\s]+$/u"; // international letters
			$namePatternNumberOfCharacters = "/^['-\p{L}\s]{2,32}$/u"; // 3-32 international letters
			if (preg_match ( $namePattern, $lastname )) {
				if (preg_match ( $namePatternNumberOfCharacters, $lastname )) {
					// first name is valid
				} else {
					$error .= "Last name has not enough or too many characters!<br/>";
					return new Response ( 200, array (
							'errorDetails' => $error 
					) );
				}
			} else {
				$error .= "Last name is only allowed lower case or upper case characters!<br/>";
				return new Response ( 200, array (
						'errorDetails' => $error 
				) );
			}
		}
		if (isset ( $requestData->username )) {
			$username = mysqli_real_escape_string ( $db, $requestData->username );
		} else {
			$error .= "Username is required!<br/>";
			return new Response ( 200, array (
					'errorDetails' => $error 
			) );
		}
		if (isset ( $requestData->email )) {
			$email = mysqli_real_escape_string ( $db, $requestData->email );
		} else {
			$error .= "Email is required!<br/>";
			return new Response ( 200, array (
					'errorDetails' => $error 
			) );
		}
		if (isset ( $requestData->password )) {
			$password = mysqli_real_escape_string ( $db, $requestData->password );
		}
		if (isset ( $requestData->password2 )) {
			$password2 = mysqli_real_escape_string ( $db, $requestData->password2 );
		}
		
		$allowInternational = false;
		if (defined ( 'PCRE_VERSION' )) {
			if (intval ( PCRE_VERSION ) >= 7) { // constant available since PHP 5.2.4
				$allowInternational = true;
			}
		}
		if (! $allowInternational) {
			$error .= "Your php version is too old!!! update php > 5.2.4<br/>";
			return new Response ( 200, array (
					'errorDetails' => $error 
			) );
		}
		
		if ($username != $this->user ['user_name']) {
			
			$usernamePattern = "/^[\p{L}\p{N}_-\.]+$/u"; // international Letters, Numbers, underscore and hyphen
			$usernamePatternNumberOfCharacters = "/^[\p{L}\p{N}_-\.]{3,30}$/u"; // international Letters, Numbers, underscore and hyphen 3 to 16 characters
			if (preg_match ( $usernamePattern, $username )) {
				// valid username check number of chars
				if (preg_match ( $usernamePatternNumberOfCharacters, $username )) {
					// username has vaild characters
					
					$usernameCheck_q = mysqli_query ( $db, $test = "SELECT * FROM users WHERE user_name='$username' AND id!='" . $this->user ['id'] . "'" ) or die ( $test . mysqli_error ( $db ) );
					if ($usernameCheck = mysqli_fetch_array ( $usernameCheck_q )) {
						$error .= "Username already exists! Please choose another Username.<br/>";
						return new Response ( 200, array (
								'errorDetails' => $error 
						) );
					}
					
					$usernameCheck_q = mysqli_query ( $db, $test = "SELECT * FROM currencies WHERE currency='$username' AND currency_steward!='" . $this->user ['id'] . "'" ) or die ( $test . mysqli_error ( $db ) );
					if ($usernameCheck = mysqli_fetch_array ( $usernameCheck_q )) {
						$error .= "Username exists as currency! Please choose another Username.<br/>";
						return new Response ( 200, array (
								'errorDetails' => $error 
						) );
					}
					
					$usernameCheck_q = mysqli_query ( $db, $test = "SELECT * FROM user_account_currencies uac, user_spaces us WHERE us.id=uac.user_space_id AND uac.trading_name='$username' AND us.user_id!='" . $this->user ['id'] . "'" ) or die ( $test . mysqli_error ( $db ) );
					if ($usernameCheck = mysqli_fetch_array ( $usernameCheck_q )) {
						$error .= "Username exists as trading name! Please choose another Username.<br/>";
						return new Response ( 200, array (
								'errorDetails' => $error 
						) );
					}
					
					$usernameCheck_q = mysqli_query ( $db, $test = "SELECT * FROM spaces s, user_spaces us WHERE us.class='steward' AND us.space_id=s.id AND s.space_name='$username' AND us.user_id!='" . $this->user ['id'] . "'" ) or die ( $test . mysqli_error ( $db ) );
					if ($usernameCheck_q = mysqli_fetch_array ( $usernameCheck_q )) {
						$error .= "Username exists as space name! Please choose another Username.<br/>";
						return new Response ( 200, array (
								'errorDetails' => $error 
						) );
					}
					
					$dotPattern = "/^([\p{L}\p{N}_-]+\.)+([\p{L}\p{N}_-]+)$/u";
					if (preg_match ( $dotPattern, $username, $matches )) {
						$user_name = $matches [0]; // contains dot
						$match = '';
						for($i = count ( $matches ) - 1; $i > 1; $i --) {
							// concatenate match to beginning of string.
							$match = $matches [$i] . $match;
							// do a space check to make sure it exists first.
							$usernameCheck_q = mysqli_query ( $db, $test = "SELECT * FROM spaces WHERE space_name='$match'" ) or die ( $test . mysqli_error ( $db ) );
							if (! ($usernameCheck = mysqli_fetch_array ( $usernameCheck_q ))) {
								$error .= "$match subspace does not exist!<br/> Please choose another Username.<br/>";
								return new Response ( 200, array (
										'errorDetails' => $error 
								) );
							}
						}
						$init_space = $match;
						// $error .= "pattern did match subspace! Username:".$user_name." Subspace:".$init_space." Please choose another Username.<br/>";
						// return new Response(200, array('errorDetails' => $error));
					} else {
						// $error .= "pattern didn't match subspace! Please choose another Username.<br/>";
						// return new Response(200, array('errorDetails' => $error));
					}
				} else {
					$error .= "Username has not enough or too many characters!<br/>";
					return new Response ( 200, array (
							'errorDetails' => $error 
					) );
				}
			} else {
				// error pattern does not match allowed characters
				$error .= "Username contains invalid characters!<br/>";
				return new Response ( 200, array (
						'errorDetails' => $error 
				) );
			}
		}
		
		$emailPattern = "/^([\p{L}\p{N}\+_\.-]+)@([\d\p{L}\.-]+)\.([\p{L}\.]{2,6})$/u";
		if (preg_match ( $emailPattern, $email )) {
			// valid email
			$emailCheck_q = mysqli_query ( $db, $test = "SELECT * FROM users WHERE email='$email' AND id!='" . $this->user ['id'] . "'" ) or die ( $test . mysqli_error ( $db ) );
			if ($emailCheck = mysqli_fetch_array ( $emailCheck_q )) {
				$error .= "Email already exists! Please choose another Email.<br/>";
				return new Response ( 200, array (
						'errorDetails' => $error 
				) );
			}
		} else {
			$error .= "Email address contains invalid characters!<br/>";
			return new Response ( 200, array (
					'errorDetails' => $error 
			) );
		}
		
		$password_update = false;
		if (strlen ( $password ) == 0 && $password == $password2) {
			// password is not being updated
		} else {
			$password_update = true;
			if (strlen ( $password ) > 0) {
				if ($password != $password2) {
					$error .= "Passwords do not match!<br/>";
					return new Response ( 200, array (
							'errorDetails' => $error 
					) );
				}
			} else {
				// password is too short
				$error .= "password is too short!<br/>";
				return new Response ( 200, array (
						'errorDetails' => $error 
				) );
			}
		}
		
		if ($error == '') {
			// no error found insert user
			
			if ($password_update) {
				$password_hash = password_hash ( $password, PASSWORD_BCRYPT );
				$user_q = mysqli_query ( $db, $test = "UPDATE users SET password='$password_hash', password2='$password_hash' WHERE id='" . $this->user ['id'] . "'" ) or die ( $test . mysqli_error ( $db ) );
			}
			
			// TODO: if space change in username add user to space!
			
			$user_q = mysqli_query ( $db, $test = "UPDATE users SET fname='$firstname', lname='$lastname', user_name='$username', email='$email' WHERE id='" . $this->user ['id'] . "'" ) or die ( $test . mysqli_error ( $db ) );
			
			// TODO: add two fields for default space and default currency
			$confirmed = false;
			
			if ($confirmed) {
				
				// make user a user of initial space
				$init_space_q = mysqli_query ( $db, $test = "SELECT * FROM spaces WHERE space_name='$init_space' ORDER BY id ASC" ) or die ( $test . mysqli_error ( $db ) );
				if ($initSpace = mysqli_fetch_array ( $init_space_q )) {
					
					$spaceID = $initSpace ['id'];
					$user_space_q = mysqli_query ( $db, $test = "INSERT INTO user_spaces (`user_id`,`space_id`,`class`) VALUES ('$userID', '$spaceID', 'user')" ) or die ( $test . mysqli_error ( $db ) );
					$userSpaceID = mysqli_insert_id ( $db );
					
					if ($userSpaceID > 0) {
						$user_currency_q = mysqli_query ( $db, $test = "SELECT * FROM currencies WHERE currency='$init_currency' ORDER BY id ASC" ) or die ( $test . mysqli_error ( $db ) );
						if ($userCurrency = mysqli_fetch_array ( $user_currency_q )) {
							$currencyID = $userCurrency ['id'];
							$user_account_currencies_q = mysqli_query ( $db, $test = "INSERT INTO user_account_currencies (`trading_name`, `user_space_id`, `currency_id`) VALUES ('$username','$userSpaceID','$currencyID') " ) or die ( $test . mysqli_error ( $db ) );
							$userAccountCurrenciesID = mysqli_insert_id ( $db );
							
							if ($userAccountCurrenciesID > 0) {
								// user account created
							} else {
								$error .= "Failed to insert trading name!!! Contact system administration!";
								// reverse inserts
								mysqli_query ( $db, $test = "DELETE FROM user_spaces WHERE id='$userSpaceID'" ) or die ( $test . mysqli_error ( $db ) );
								mysqli_query ( $db, $test = "DELETE FROM users WHERE id='$userID'" ) or die ( $test . mysqli_error ( $db ) );
								return new Response ( 200, array (
										'errorDetails' => $error 
								) );
							}
						} else {
							$error .= "Failed to find default user currency!!! Contact system administration!";
							// reverse inserts
							mysqli_query ( $db, $test = "DELETE FROM user_spaces WHERE id='$userSpaceID'" ) or die ( $test . mysqli_error ( $db ) );
							mysqli_query ( $db, $test = "DELETE FROM users WHERE id='$userID'" ) or die ( $test . mysqli_error ( $db ) );
							return new Response ( 200, array (
									'errorDetails' => $error 
							) );
						}
					} else {
						$error .= "Failed to insert user into default space!!! Contact system administration!";
						// reverse inserts
						mysqli_query ( $db, $test = "DELETE FROM users WHERE id='$userID'" ) or die ( $test . mysqli_error ( $db ) );
						return new Response ( 200, array (
								'errorDetails' => $error 
						) );
					}
				} else {
					$error .= "Could not find default space !!! Contact system administration!";
					// reverse inserts
					mysqli_query ( $db, $test = "DELETE FROM users WHERE id='$userID'" ) or die ( $test . mysqli_error ( $db ) );
					return new Response ( 200, array (
							'errorDetails' => $error 
					) );
				}
				
				if ($error == '') {
					// create personal space
					$created = date ( "Y-m-d H:i:s" );
					$space_create_q = mysqli_query ( $db, $test = "INSERT INTO spaces (`space_name`,`created`) VALUES ('$username', '$created') " ) or die ( $test . mysqli_error ( $db ) );
					$spaceID = mysqli_insert_id ( $db );
					
					if ($spaceID > 0) {
						
						// make user a steward of their space
						
						$user_space_q = mysqli_query ( $db, $test = "INSERT INTO user_spaces (`user_id`,`space_id`,`class`) VALUES ('$userID', '$spaceID', 'steward')" ) or die ( $test . mysqli_error ( $db ) );
						if ($userSpaceID = mysqli_insert_id ( $db )) {
						} else {
							$error .= "Could not make user a steward of personal space!!! Contact system administration!";
							// reverse inserts
							mysqli_query ( $db, $test = "DELETE FROM user_spaces WHERE id='$userSpaceID'" ) or die ( $test . mysqli_error ( $db ) );
							mysqli_query ( $db, $test = "DELETE FROM users WHERE id='$userID'" ) or die ( $test . mysqli_error ( $db ) );
							mysqli_query ( $db, $test = "DELETE FROM spaces WHERE id='$spaceID'" ) or die ( $test . mysqli_error ( $db ) );
							return new Response ( 200, array (
									'errorDetails' => $error 
							) );
						}
					} else {
						$error .= "Could not create personal space!!! Contact system administration!";
						// reverse inserts
						mysqli_query ( $db, $test = "DELETE FROM user_spaces WHERE id='$userSpaceID'" ) or die ( $test . mysqli_error ( $db ) );
						mysqli_query ( $db, $test = "DELETE FROM users WHERE id='$userID'" ) or die ( $test . mysqli_error ( $db ) );
						return new Response ( 200, array (
								'errorDetails' => $error 
						) );
					}
				}
			}
		}
		
		if ($error == '') {
			
			$accounts_array = array ();
			$default_count = 0;
			$accounts_q = mysqli_query ( $db, $test = "SELECT *, uac.id user_account_currencies_id FROM user_account_currencies uac, user_spaces us, currencies c  WHERE uac.currency_id=c.id AND uac.user_space_id=us.id AND us.user_id='" . $this->user ['id'] . "' ORDER BY uac.id ASC" ) or die ( $test . mysqli_error ( $db ) );
			while ( $accounts = mysqli_fetch_array ( $accounts_q ) ) {
				$default = false;
				if (($accounts ['currency_id'] == 1) && ($default_count == 0)) {
					$default_count ++;
					$default = true;
				}
				
				array_push ( $accounts_array, array (
						"id" => intval ( $accounts ['user_account_currencies_id'] ),
						"type" => array (
								"id" => intval ( $accounts ['user_account_currencies_id'] ),
								"name" => $accounts ['trading_name'],
								"currency" => array (
										"id" => intval ( $accounts ['currency_id'] ),
										"symbol" => $accounts ['currency'],
										"name" => $accounts ['currency'] 
								) 
						),
						"default" => $default 
				) );
			}
			$result = new Response ( 200, array (
					'profile' => array (
							'id' => intval ( $this->user ['id'] ),
							'name' => $firstname . " " . $lastname,
							'firstname' => $firstname,
							'lastname' => $lastname,
							'username' => $username,
							'email' => $email,
							'customValues' => array (
									array (
											"internalName" => "gender",
											"fieldId" => 2,
											"displayName" => "Gender",
											"value" => "Male",
											"possibleValueId" => 1 
									),
									array (
											"internalName" => "address",
											"fieldId" => 3,
											"displayName" => "Address",
											"value" => "1234 my Street" 
									),
									array (
											"internalName" => "postalCode",
											"fieldId" => 4,
											"displayName" => "Postal code",
											"value" => "V8N 0R2" 
									),
									array (
											"internalName" => "city",
											"fieldId" => 5,
											"displayName" => "City",
											"value" => "Victoria" 
									) 
							) 
					),
					'requireTransactionPassword' => false,
					'accounts' => $accounts_array,
					'canMakeMemberPayments' => true,
					'canMakeSystemPayments' => false,
					'decimalCount' => 2,
					'decimalSeparator' => "." 
			) );
		} else {
			$result = new Response ( 200, array (
					'errorDetails' => $error 
			) );
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
	protected function json() {
		$this->before ( function ($request) {
			if ($request->contentType == "application/json") {
				$request->data = json_decode ( $request->data );
			}
		} );
		$this->after ( function ($response) {
			$response->contentType = "application/json";
			if (isset ( $_GET ['jsonp'] )) {
				$response->body = $_GET ['jsonp'] . '(' . json_encode ( $response->body ) . ');';
			} else {
				$response->body = json_encode ( $response->body );
			}
		} );
	}
}

/**
 * The @uri annotation routes requests that match that URL to this resource. Multiple
 * annotations allow this resource to match multiple URLs.
 *
 * @uri /access/forgotPassword
 */
class accessForgotPassword extends Resource {
	private $user;
	private $username;
	
	/**
	 * The setup() method is called when the resource is executed.
	 * We don't do this check
	 * within the resource constructor as we can't cleanly throw an exception from within
	 * an object constructor.
	 */
	function setup() {
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
	 * @method POST
	 *         @accepts application/json
	 *         @provides application/json
	 *         @json
	 *         @cache 0
	 * @return Tonic\Response
	 */
	public function sayaccessForgotPassword() {
		require ("rest_connect.php");
		require ("../config.php");
		require ("../password.php");
		function email_letter($to, $from, $subject = 'no subject', $msg = 'no msg') {
			$headers = "From: $from\r\n";
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
			$headers .= 'X-Mailer: PHP/' . phpversion ();
			return mail ( $to, $subject, $msg, $headers );
		}
		
		$requestData = $this->request->data;
		
		$username = "";
		$email = "";
		
		$error = "";
		
		if (isset ( $requestData->email )) {
			$email = mysqli_real_escape_string ( $db, $requestData->email );
		} else {
			$error .= "Email is required!<br/>";
			return new Response ( 200, array (
					'errorDetails' => $error 
			) );
		}
		
		$allowInternational = false;
		if (defined ( 'PCRE_VERSION' )) {
			if (intval ( PCRE_VERSION ) >= 7) { // constant available since PHP 5.2.4
				$allowInternational = true;
			}
		}
		if (! $allowInternational) {
			$error .= "Your php version is too old!!! update php > 5.2.4<br/>";
			return new Response ( 200, array (
					'errorDetails' => $error 
			) );
		}
		
		$emailPattern = "/^([\p{L}\p{N}\+_\.-]+)@([\p{N}\p{L}\.-]+)\.([\p{L}\.]{2,6})$/u";
		if (preg_match ( $emailPattern, $email )) {
			// valid email
			$emailCheck_q = mysqli_query ( $db, $test = "SELECT * FROM users WHERE email='$email'" ) or die ( $test . mysqli_error ( $db ) );
			if ($emailCheck = mysqli_fetch_array ( $emailCheck_q )) {
				// email passed check send email with password reset link
				
				$reset_key = strtotime ( "now" ) * rand ();
				$reset_hash = password_hash ( ( string ) $reset_key, PASSWORD_BCRYPT );
				
				// update key on user table, then verify in resetPassword.php
				mysqli_query ( $db, $test = "UPDATE users SET password2 = '$reset_key' WHERE email='$email'" ) or die ( $test . mysqli_error ( $db ) );
				
				$msg = "To Reset your password click on this link <a href='{$CFG->url}/resetPassword.php?email=" . urlencode ( $email ) . "&reset=" . urlencode ( $reset_hash ) . "'>Reset Password</a>";
				$msg .= "<p>OpenMoney IT Team</p>";
				$msg .= "If you did not initiate the forgot password link request then ignore this and your password will remain the same.";
				
				$subject = "{$CFG->site_name}: Forgotten password reset REQUESTED for $email";
				$dear = ($emailCheck ['fname'] == '') ? $emailCheck ['user_name'] : $emailCheck ['fname'] . " " . $emailCheck ['lname'];
				
				$sentEmail = email_letter ( "\"" . $dear . "\"<" . $email . ">", $CFG->system_email, $subject, $msg );
				return new Response ( 200, array (
						'sentEmail' => $sentEmail 
				) );
			} else {
				$error .= "Email address was not found!<br/>";
				return new Response ( 200, array (
						'errorDetails' => $error 
				) );
			}
		} else {
			// if it's not an email then maybe it's a username!
			$username = $email;
			$usernamePattern = "/^[\p{L}\p{N}_-\.]+$/u"; // international Letters, Numbers, underscore and hyphen
			$usernamePatternNumberOfCharacters = "/^[\p{L}\p{N}_-\.]{1,30}$/u"; // international Letters, Numbers, underscore and hyphen 1 to 30 characters
			if (preg_match ( $usernamePattern, $username )) {
				// valid username check number of chars
				if (preg_match ( $usernamePatternNumberOfCharacters, $username )) {
					// username has vaild characters
					
					$usernameCheck_q = mysqli_query ( $db, $test = "SELECT * FROM users WHERE user_name='$username'" ) or die ( $test . mysqli_error ( $db ) );
					if ($usernameCheck = mysqli_fetch_array ( $usernameCheck_q )) {
						
						$real_email = $usernameCheck ['email'];
						
						$reset_key = ( string ) (strtotime ( "now" ) * rand ());
						$reset_hash = password_hash ( $reset_key, PASSWORD_BCRYPT );
						
						// update key on user table, then verify in resetPassword.php
						mysqli_query ( $db, $test = "UPDATE users SET password2 = '$reset_key' WHERE user_name='$username'" ) or die ( $test . mysqli_error ( $db ) );
						
						$msg = "To Reset your password click on this link <a href='{$CFG->url}/resetPassword.php?username=" . urlencode ( $username ) . "&reset=" . urlencode ( $reset_hash ) . "'>Reset Password</a>";
						$msg .= "<p>OpenMoney IT Team</p>";
						$msg .= "If you did not initiate the forgot password link request then ignore this and your password will remain the same.";
						
						$subject = "{$CFG->site_name}: Forgotten password reset REQUESTED for $email";
						$sentEmail = email_letter ( $real_email, $CFG->system_email, $subject, $msg );
						return new Response ( 200, array (
								'sentEmail' => $sentEmail 
						) );
					} else {
						$error .= "Username was not found!<br/>";
						return new Response ( 200, array (
								'errorDetails' => $error 
						) );
					}
				} else {
					$error .= "Username has invalid size!<br/>";
					return new Response ( 200, array (
							'errorDetails' => $error 
					) );
				}
			} else {
				// error pattern does not match allowed characters
				$error .= "Username has invalid characters!<br/>";
				return new Response ( 200, array (
						'errorDetails' => $error 
				) );
			}
		}
		
		$error .= "Invalid Email or Username!<br/>";
		return new Response ( 200, array (
				'errorDetails' => $error 
		) );
	}
	
	/**
	 * Condition method to turn output into JSON.
	 *
	 * This condition sets a before and an after filter for the request and response. The
	 * before filter decodes the request body if the request content type is JSON, while the
	 * after filter encodes the response body into JSON.
	 */
	protected function json() {
		$this->before ( function ($request) {
			if ($request->contentType == "application/json") {
				$request->data = json_decode ( $request->data );
			}
		} );
		$this->after ( function ($response) {
			$response->contentType = "application/json";
			if (isset ( $_GET ['jsonp'] )) {
				$response->body = $_GET ['jsonp'] . '(' . json_encode ( $response->body ) . ');';
			} else {
				$response->body = json_encode ( $response->body );
			}
		} );
	}
}
