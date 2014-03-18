<?php
namespace Tfs;

class HttpResponse
{
    private $result;
    private $statusCode;
    private $content;

    public static $MESSAGES = array(
        // 1xx: Informational - Request received, continuing process
        100 => 'Continue',
        101 => 'Switching Protocols',

        // 2xx: Success - The action was successfully received, understood and
        // accepted
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',

        // 3xx: Redirection - Further action must be taken in order to complete
        // the request
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',  // 1.1
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',

        // 4xx: Client Error - The request contains bad syntax or cannot be
        // fulfilled
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',

        // 5xx: Server Error - The server failed to fulfill an apparently
        // valid request
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        509 => 'Bandwidth Limit Exceeded',
    );
    
    public function __construct($statusCode=null, $content=null)
    {
        if ( null !== $statusCode ) {
            $this->setStatusCode($statusCode);
        }
        if ( null !== $content ) {
            $this->setContent($content);
        }
    }

    public function __get($name)
    {
        $result = $this->getResult();
        return isset($result->$name) ? $result->$name : null;
    }
    
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    public function getResult()
    {
        if ( !isset($this->result) && $this->isOk() ) {
            $this->result = json_decode($this->content);
        }
        return $this->result;
    }
    
    public function isOk()
    {
        return in_array($this->statusCode, array(200, 201, 204));
    }

    public function isClientError()
    {
        return $this->statusCode == 400;
    }

    public function isUnauthorized()
    {
        return $this->statusCode == 401;
    }

    public function isNotFound()
    {
        return $this->statusCode == 404;
    }
    
    public function isConflict()
    {
        return $this->statusCode == 409;
    }

    public function isRemoteError()
    {
        return in_array($this->statusCode, array(500, 502, 504));
    }

    public function getCode()
    {
        return $this->statusCode;
    }
    
    public function getMessage()
    {
        return isset(self::$MESSAGES[$this->statusCode]) ? self::$MESSAGES[$this->statusCode] : 'Unknown status code';
    }
}
