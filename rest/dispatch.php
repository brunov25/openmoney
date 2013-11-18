<?php

// load autoloader (delete as appropriate)
if (@include(__DIR__.'/../tonic-3.2/src/Tonic/Autoloader.php')) { // use Tonic autoloader
    //new Tonic\Autoloader('openmoney'); // add another namespace
} elseif (!@include(__DIR__.'/../tonic-3.2/vendor/autoload.php')) { // use Composer autoloader
    die('Could not find autoloader');
}

$config = array(
    'load' => array(
        __DIR__.'/src/openmoney/*.php' // load example resources
    ),
    #'mount' => array('Tyrell' => '/nexus'), // mount in example resources at URL /nexus
    #'cache' => new Tonic\MetadataCacheFile('/tmp/tonic.cache') // use the metadata cache
    #'cache' => new Tonic\MetadataCacheAPC // use the metadata cache
);

$app = new Tonic\Application($config);

#echo $app; die;

$request = new Tonic\Request();



#echo $request; die;

try {

    $resource = $app->getResource($request);

    #echo $resource; die;
    
    

    $response = $resource->exec();


} catch (Tonic\NotFoundException $e) {
    $response = new Tonic\Response(404, $e->getMessage());

} catch (Tonic\UnauthorizedException $e) {
    $response = new Tonic\Response(401, $e->getMessage());
    $response->wwwAuthenticate = 'Basic realm="My Realm"';

} catch (Tonic\MethodNotAllowedException $e) {
    $response = new Tonic\Response($e->getCode(), $e->getMessage());
    $response->allow = $resource->allowedMethods();
} catch (Tonic\Exception $e) {
    $response = new Tonic\Response($e->getCode(), $e->getMessage());
} 

#echo $response;

$response->accessControlAllowOrigin = "*";
$response->accessControlAllowMethods = "HEAD,GET,POST,PUT,DELETE,OPTIONS";
$response->accessControlAllowHeaders = "Accept, Cache-Control, Pragma, Origin, Authorization, Content-Type, X-Requested-With";
$response->output();
