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
     */
    public function welcomeMessage()
    {
        $body = <<<END
<!doctype html>
<title>Welcome</title>
<h1>Welcome to the Tonic micro-framework library</h1>
<p>If you are seeing this message, then everything is working as expected.</p>
<p>The openmoney namespace contains some example Resource classes to get you started.</p>

<h2>initialData world - src/openmoney/initialData.php</h2>
<p><a href="initialData">initialData world</a> - Get the default representation of the initialData world example</p>
<p><a href="initialData.html">initialData HTML</a> - Get the HTML representation</p>
<p><a href="initialData.json">initialData JSON</a> - Get the JSON representation</p>
<p><a href="initialData.fr">Bonjour</a> - Say initialData in French</p>
<p><a href="initialData/mars">initialData mars</a> - Say initialData to mars</p>
<p><a href="initialData/deckard">Deckard</a> - Say initialData to Rick Deckard</p>
<p><a href="initialData/roy">Roy</a> - Say initialData to Roy Batty</p>

<h2>Simple HTTP authentication - src/openmoney/Secret.php</h2>
<p><a href="secret">Secure a single resource method</a> - use aUser/aPassword to see the secret</p>
<p><a href="secret2">Secure an entire resource</a> - use aUser2/aPassword2 to see the secret</p>

<hr>
<p>Make sure you read the <a href="https://github.com/peej/tonic/blob/master/README.markdown">README.markdown</a> file.</p>
<p>If you require a full example, checkout <a href="https://github.com/peej/tonic/tree/example">the "example" branch from the Tonic git repo</a>.</p>
END;
        return new Response(Response::OK, $body, array(
            'content-type' => 'text/html'
        ));
    }

}