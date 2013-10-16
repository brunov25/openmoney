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
	 * @cache 0
	 * @return Tonic\Response
	 */
	public function saygeneral()
	{

			$result = new Response(200, array("cyclosVersion" => "3.7.4",
 											   "applicationName" => "openmoney.ca",
 											   "welcomeMessage" => "Welcome to the open money LETSystem",
 											   "principalType" => "USER",
 											   "credentialType" => "LOGIN_PASSWORD",
											   'images' => array(array('id' => 241,
																        'caption' => 'mobileSplash_large',
																        'thumbnailUrl' => 'http://openmoney.ca/beta/webclient/res/icon/android/icon-72-hdpi.png',
																        'fullUrl' => 'http://openmoney.ca/beta/webclient/res/screen/android/screen-hdpi-portrait.png',
																        'lastModified' => '2013-10-01T17:02:06.000+0000'),
											   					 array('id' => 240,
																        'caption' => 'mobileSplash_medium',
																        'thumbnailUrl' => 'http://openmoney.ca/beta/webclient/res/icon/android/icon-48-mdpi.png',
																        'fullUrl' => 'http://openmoney.ca/beta/webclient/res/screen/android/screen-mdpi-portrait.png',
																        'lastModified' => '2013-10-01T17:02:06.000+0000'),
											   					 array('id' => 207,
																        'caption' => 'mobileSplash_small',
																        'thumbnailUrl' => 'http://openmoney.ca/beta/webclient/res/icon/android/icon-36-ldpi.png',
																        'fullUrl' => 'http://openmoney.ca/beta/webclient/res/screen/android/screen-ldpi-portrait.png',
																        'lastModified' => '2013-10-01T17:02:05.000+0000'))));

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