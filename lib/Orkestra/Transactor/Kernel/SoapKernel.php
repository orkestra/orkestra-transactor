<?php

namespace Orkestra\Transactor\Kernel;

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response;

/**
 * Soap Kernel
 *
 * This kernel provides a wrapper for SoapClient to execute a given Request and return
 * a normalized Response object.
 *
 * @package Orkestra
 * @subpackage Transactor
 */
class SoapKernel implements IKernel
{
    /**
     * {@inheritdoc}
     */
    public function handle(Request $request)
    {
        if (!$request->has('action')) {
            throw new KernelException('Unable to process request. No action specified');
        }
        
        $client = new \SoapClient($request->getUri(), array(
        	"trace" => 1,
        	"exceptions" => true,
        ));
        
        $headers = $request->headers->all();
        $content = $request->getContent();
        $action = $request->get('action');
        
        try {
            $rawResponse = $client->__soapCall($action, $content, array(), $headers);
            
            $body = $rawResponse;
            $code = 200;
        }
        catch (\Exception $e) {
            $body = $e->getMessage();
            $code = 500;
        }
        
        $headers = $client->__getLastResponseHeaders();
    
        $response = new Response($body, $code, $headers);
        
        return $response;
    }
}