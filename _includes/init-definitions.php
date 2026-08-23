<?php

// Defining constants and configuring additional error handling

// Defining page types (default is "main")
define (    "PAGE_MAIN",            "main");
define (    "PAGE_ERROR",           "error");
define (    "PAGE_SUB_BLOG",        "blog");
define (    "PAGE_SUB_ELEMENT",     "element");
define (    "PAGE_SUB_PUB",         "publication");

// Defining log levels (Using built-in PHP constants)
// define ( "LOG_INFO",             LOG_INFO);
// define ( "LOG_WARNING",          LOG_WARNING);
// define ( "LOG_ERR",              LOG_ERR);

// (Defining serve results for file and citation serving)
define (    "SERVE_SUCCESS",        200);
define (    "SERVE_ERROR_REQUEST",  400);
define (    "SERVE_ERROR_NOACCESS", 403);
define (    "SERVE_ERROR_NOFILE",   404);

// Error handling for file lookup and serving
class ServeException extends \RuntimeException
{
    public function __construct(string $message, private int $status = 404)
    {
        parent::__construct($message);
    }
    public function getStatus(): int
    {
        return $this->status;
    }
}

?>