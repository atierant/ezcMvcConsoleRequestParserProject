<?php
/**
 * File containing the interface of the ezcMvcConsoleRequestParser class
 *
 * @copyright Copyright (C) 2005-2010 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @version 1.1.3
 * @filesource
 * @package MvcTools
 */

/**
 * Request parser that uses Command line to populate an ezcMvcRequest object.
 *
 * @package MvcTools
 * @version 1.1.3
 * @mainclass
 */
interface IEzcMvcConsoleRequestParser
{
    /**
     * Uses the data from the CL.
     *
     * @return ezcMvcRequest
     */
    public function createRequest();

    /**
     * Creates and returns an ezcMvcRequest object.
     *
     * @return ezcMvcRequest
     */
    protected function createRequestObject();

    /**
     * Processes the basic CL variables variables is set
     */
    protected function processAuthVars();

    /**
     * Processes the standard headers that are not subdivided into other structs.
     */
    protected function processStandardHeaders();

    /**
     * Processes the request protocol. 
     */
    protected function processProtocol();

    /**
     * Processes the request host.
     */
    protected function processHost();

    /**
     * Processes the request date.
     */
    protected function processDate();

    /**
     * Processes the request variables.
     */
    protected function processVariables();

    /**
     * Processes the referrer.
     */
    protected function processReferrer();

    /**
     * Processes the request URI.
     */
    protected function processUri();

    /**
     * Processes the request ID from host and URI.
     */
    protected function processRequestId();

    /**
     * Processes the request body for PUT requests.
     */
    protected function processBody();

    /**
     * Proccesses the HTTP Accept headers into the ezcMvcRequestAccept struct.
     */
    protected function processAcceptHeaders();

    /**
     * Proccesses the User Agent header into the ezcMvcRequestUserAgent struct.
     */
    protected function processUserAgentHeaders();

    /**
     * Processes uploaded files.
     */
    /* protected function processFiles(); // Inutile, non accepté par le CLI

    /**
     * Process cookies
     */
    protected function processCookies();
}
?>
