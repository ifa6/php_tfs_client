<?php
namespace Tfs;

class HttpClient
{
    private $curlHandler;
    private $baseUrl;
    private $timeout = 5;
    private $connectTimeout = 5;
    private $headers = array();
    private $userAgent = 'HttpRequest (http://php.net) PHP/5';

    private $lastResponse;

    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
        return $this;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function setHeaders($headers)
    {
        $this->headers = $headers;
        return $this;
    }

    public function setHeader($name, $value)
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function getUserAgent()
    {
        return $this->userAgent;
    }

    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;
        return $this;
    }

    public function getTimeout()
    {
        return $this->timeout;
    }

    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
        return $this;
    }

    public function getConnectTimeout()
    {
        return $this->connectTimeout;
    }

    public function setConnectTimeout($connectTimeout)
    {
        $this->connectTimeout = $connectTimeout;
        return $this;
    }

    public function getLastResponse()
    {
        return $this->lastResponse;
    }

    public function close()
    {
        if ( $this->curlHandler ) {
            curl_close($this->curlHandler);
            $this->curlHandler = null;
        }
    }

    public function __destruct()
    {
        $this->close();
    }
    
    protected function prepareCurlHandler($options)
    {
        if ( !isset($this->curlHandler) ) {
            $this->curlHandler = curl_init();
        }
        $options = $options + array(
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => $this->connectTimeout,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_HTTPHEADER => $this->prepareHeaders(),
            CURL_HTTP_VERSION_1_1 => true,
        );
        curl_setopt_array($this->curlHandler, $options);
        return $this;
    }
    
    protected function prepareHeaders()
    {
        if ( !isset($this->headers['User-Agent']) ) {
            $this->headers['User-Agent'] = $this->userAgent;
        }
        $headers = array();
        foreach ( $this->headers as $name => $value ) {
            $headers[] = $name . ': ' . $value;
        }
        return $headers;
    }

    protected function prepareUrl($url)
    {
        return (substr($url,0,1) == '/' ? $this->getBaseUrl() . $url : $url);
    }

    protected function send()
    {
        $ch = $this->curlHandler;
        $ret = curl_exec($ch);
        if ( false === $ret ) {
            $error_code = curl_errno($ch);
            throw new \RuntimeException("Curl Request Failed: [{$error_code}] ". curl_error($ch), $error_code);
        }
        return $this->lastResponse = new HttpResponse(curl_getinfo($ch, CURLINFO_HTTP_CODE), $ret);
    }
    
    public function get($url)
    {
        return $this->prepareCurlHandler(array(
            CURLOPT_URL => $this->prepareUrl($url),
            CURLOPT_CUSTOMREQUEST => 'GET',
        ))->send();
    }

    public function put($url, $content=null)
    {
        return $this->prepareCurlHandler(array(
            CURLOPT_URL => $this->prepareUrl($url),
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POSTFIELDS => $content,
        ))->send();
    }

    public function post($url, $content=null)
    {
        return $this->prepareCurlHandler(array(
            CURLOPT_URL => $this->prepareUrl($url),
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $content,
            CURLOPT_POST => true
        ))->send();
    }

    public function delete($url)
    {
        return $this->prepareCurlHandler(array(
            CURLOPT_URL => $this->prepareUrl($url),
            CURLOPT_CUSTOMREQUEST => 'DELETE',
        ))->send();
    }

    public function head($url)
    {
        return $this->prepareCurlHandler(array(
            CURLOPT_URL => $this->prepareUrl($url),
            CURLOPT_CUSTOMREQUEST => 'HEAD',
            CURLOPT_NOBODY => true
        ))->send();
    }
}
