<?php

namespace Orkestra\Transactor\Kernel;

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response;

/**
 * Http Kernel
 *
 * This kernel provides a wrapper for cURL to execute a given Request and return
 * a normalized Response object.
 *
 * @package Orkestra
 * @subpackage Transactor
 */
class HttpKernel implements IKernel
{
    /**
     * {@inheritdoc}
     */
    public function handle(Request $request)
    {
        $ch = curl_init($request->getUri());
        
        $headers = $this->_convertHeadersToCurlFormat($request->headers->all());
        $params = $request->request->all();
        
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request->getMethod());
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        
        $rawResponse = curl_exec($ch);
        $info = curl_getinfo($ch);
        
        $headers = explode("\n", substr($rawResponse, 0, $info['header_size']));
        $body = ltrim(substr($rawResponse, $info['header_size']));
        $code = $info['http_code'];
        
        $response = new Response($body, $code, $headers);
        
        return $response;
    }
    
    /** 
     * Convert Headers To Curl Format
     *
     * Converts a Request object's header format into the format used by cURL
     *
     * @param array $headers
     * @return array
     */
    protected function _convertHeadersToCurlFormat(array $headers)
    {
        $result = array();
        
        foreach ($headers as $directive => $value) {
            if (is_array($value)) {
                $value = $value[0];
            }
            
            $result[] = "{$directive}: {$value}";
        }
        
        return $result;
    }
}