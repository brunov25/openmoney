<?php

namespace openmoney;

use Tonic\Resource,
    Tonic\Response;

/**
 * Introduction resource to the examples.
 *
 * Creates a HTML resource at the root of your Tonic application that explains and links
 * to the other example resources within the openmoney namespace.
 *
 * @uri /
 */
class Welcome extends Resource
{
    /**
     * Returns the welcome message.
     * @method GET
     * @cache 0
     */
    public function welcomeMessage()
    {
        $body = <<<END
<!doctype html>
<title>
Openmoney Restful Framework
</title>
<h1>Openmoney Restful Framework</h1>
here you find the general documentation for the openmoney omlets Restful services
go here to get the <a href="general">General Data</a> <br/>
initial account data is accessable from here <a href="access/initialData">Initial Data</a><br/>
END;
        return new Response(Response::OK, $body, array(
            'content-type' => 'text/html'
        ));
    }

}
