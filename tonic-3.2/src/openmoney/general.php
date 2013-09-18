<?php

namespace openmoney;

use Tonic;
use Tonic\Resource,
    Tonic\Response,
    Tonic\ConditionException;

/**
 * The obligitory general World example
 *
 * The @uri annotation routes requests that match that URL to this resource. Multiple
 * annotations allow this resource to match multiple URLs.
 *
 * @uri /general
 * @uri /general/:name
 */
class general extends Resource
{
	
	
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
	 * @param  str $name
	 * @provides application/json
	 * @json
	 * @return Tonic\Response
	 */
	public function saygeneral($name = 'test')
	{
		if($name='test'){
			$result = new Response(200, array("cyclosVersion" => "3.7.4",
 											   "applicationName" => "openmoney.proudleo.com",
 											   "welcomeMessage" => "Welcome to the open money LETSystem",
 											   "principalType" => "USER",
 											   "credentialType" => "LOGIN_PASSWORD",
											   'images' => array(array('id' => 241,
																        'caption' => 'mobileSplash_large',
																        'thumbnailUrl' => 'http://cyclos.proudleo.com:8080/cyclos/thumbnail?id=241',
																        'fullUrl' => 'http://cyclos.proudleo.com:8080/cyclos/image?id=241',
																        'lastModified' => '2013-08-27T17:02:06.000+0000'),
											   					 array('id' => 240,
																        'caption' => 'mobileSplash_medium',
																        'thumbnailUrl' => 'http://cyclos.proudleo.com:8080/cyclos/thumbnail?id=240',
																        'fullUrl' => 'http://cyclos.proudleo.com:8080/cyclos/image?id=240',
																        'lastModified' => '2013-08-27T17:02:06.000+0000'),
											   					 array('id' => 207,
																        'caption' => 'mobileSplash_small',
																        'thumbnailUrl' => 'http://cyclos.proudleo.com:8080/cyclos/thumbnail?id=207',
																        'fullUrl' => 'http://cyclos.proudleo.com:8080/cyclos/image?id=207',
																        'lastModified' => '2013-08-27T17:02:05.000+0000'))));
		} else {
			throw new Tonic\NotFoundException;
		}
		
		return $result;
	}
	
	/**
	 * Condition method for above methods.
	 *
	 * Only allow specific :name parameter to access the method
	 */
	protected function only($allowedName)
	{
		if (strtolower($allowedName) != strtolower($this->name)) throw new ConditionException;
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