<?php
/**
 * File containing the ezcMvcConsoleRequestParser class
 *
 * @copyright Copyright (C) 2005-2010 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @version 1.1.3
 * @filesource
 * @package MvcTools
 */

/**
 * Request parser that uses CLI to populate an ezcMvcRequest object.
 *
 * @package MvcTools
 * @version 1.1.3
 * @mainclass
 */
class ezcMvcConsoleRequestParser extends ezcMvcRequestParser
{
    /**
     * Reads the request data from the command line with what ever means necessary and
     * constructs an ezcMvcRequest object.
     * Inherited function from the abstract one of ezcMvcRequestParser
     *
     * @return ezcMvcRequest
     */
    public function createRequest()
    {
        $this->request = $this->createRequestObject();

        $this->processStandardContent();
        $this->processAccept();
        $this->processUserAgent();

        $this->request->raw = &$_SERVER;

        return $this->request;
    }

    /**
     * Creates and returns an ezcMvcRequest object.
     *
     * @return ezcMvcRequest
     */
    protected function createRequestObject()
    {
        return new ezcMvcRequest();
    }

    /**
     * Processes the standard content that is not subdivided into other structs.
     */
    protected function processStandardContent()
    {
/**/    $this->processProtocol();	// Protocol description in a normalized form
/**/    $this->processHost();		// Hostname of the requested server
/**/    $this->processDate();		// Date of the request
/**/    $this->processVariables();	// Request variables
/**/    $this->processReferrer();	// Request ID of the referring URI in the same format as $requestId
/**/    $this->processUri();		// Uri of the requested resource
/**/    $this->processBody();		// Request body
/**/    $this->processRequestId();	// Full Uri - combination of host name and uri in a protocol independent order
    }


    /**
     * Processes the request protocol. 
     */
    protected function processProtocol()
    {
        $req = $this->request;
	$req->protocol = 'cli';
    }

    /**
     * Processes the request host.
     */
    protected function processHost()
    {
	$this->request->host = null;
    }

    /**
     * Processes the request date.
     */
    protected function processDate()
    {
        $this->request->date = isset( $_SERVER['REQUEST_TIME'] )
            ? new DateTime( "@{$_SERVER['REQUEST_TIME']}" )
            : new DateTime();
    }

    /**
     * Processes the request variables.
     */
    protected function processVariables()
    {
        $this->request->variables =& $_REQUEST;
    }

    /**
     * Processes the referrer.
     */
    protected function processReferrer()
    {
	$this->request->referrer = null;
    }

    /**
     * Processes the request URI.
     */
    protected function processUri()
    {
        $this->request->uri = null;
    }

    /**
     * Processes the request ID from host and URI.
     */
    protected function processRequestId()
    {
        $this->request->requestId = null;
    }

    /**
     * Processes the request body for PUT requests.
     */
    protected function processBody()
    {
        $req = $this->request;
	$arguments = $_SERVER["argv"];

        if ( $req->protocol == 'cli' )
        {
            $req->body = $arguments;
        }
    }

    /**
     * Proccesses the Accept part into the ezcMvcRequestAccept struct.
     * 
     * $accept : Request content type informations.
     * object ezcMvcRequestAccept : Struct which defines client-acceptable contents
     * Member variables of this object :
     *     $types	array 	 
     *     $charsets	array 	 
     *     $languages	array 	 
     *     $encodings	array 	 
     */
    protected function processAccept()
    {
        $this->request->accept = new ezcMvcRequestAccept;
        $accept = $this->request->accept;

	// Encodings
	$accept->encodings = array();

	// Charsets
	if ( !isset( $_SERVER['LANG'] ) )
	{
		$accept->charsets = array();
	}
	else
	{
		$accept->charsets = array();
		$charsetTmp = explode( '.', $_SERVER['LANG'] );
		$accept->charsets[0] = $charsetTmp[1];
	}

	// Languages
	if ( !isset( $_SERVER['LANGUAGE'] ) )
	{
		$accept->languages = array();
	}
	else
	{
		$accept->languages = array();
		$accept->languages = explode( ':', $_SERVER['LANGUAGE'] );
	}

	// Types
	$accept->types = array();
    }

    /**
     * Proccesses the UserAgent (terminal used) into the ezcMvcRequestUserAgent struct.
     * @ote Returns the terminal type
     */
    protected function processUserAgent()
    {
        $this->request->agent = new ezcMvcRequestUserAgent;
        $agent = $this->request->agent;

        $agent->agent = isset( $_SERVER['TERM'] )
            ? $_SERVER['TERM']
            : null;
    }
}
?>
