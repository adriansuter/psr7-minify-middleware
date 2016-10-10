<?php

namespace AdrianSuter\PSR7\Middleware\Test;

use AdrianSuter\PSR7\Middleware\Minify;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Stream;

class RendererTest extends TestCase
{

    public function testDefault()
    {
        $middleware = new Minify($this->_streamInterfaceCallback());
        $request = ServerRequestFactory::fromGlobals([]);
        $response = new HtmlResponse('');

        /** @var HtmlResponse $response */
        $response = $middleware($request, $response, function (RequestInterface $request, ResponseInterface $response) {
            $response->getBody()->write('<p>A</p>        <p>B</p>');
            return $response;
        });

        $body = $response->getBody();
        $body->rewind();

        $this->assertSame('<p>A</p> <p>B</p>', $body->getContents());
    }

    public function testPre()
    {
        $middleware = new Minify($this->_streamInterfaceCallback());
        $request = ServerRequestFactory::fromGlobals([]);
        $response = new HtmlResponse('');

        /** @var HtmlResponse $response */
        $response = $middleware($request, $response, function (RequestInterface $request, ResponseInterface $response) {
            $response->getBody()->write('<p>A</p>     <p>B</p>     <pre>var i = 0; var s = "<p>A</p>     <p>B</p>";</pre>');
            return $response;
        });

        $body = $response->getBody();
        $body->rewind();

        $this->assertSame('<p>A</p> <p>B</p> <pre>var i = 0; var s = "<p>A</p>     <p>B</p>";</pre>', $body->getContents());
    }

    /**
     * Tests the tags to be ignored.
     */
    public function testIgnoredTags()
    {
        $middleware = new Minify($this->_streamInterfaceCallback(), ['script']);
        $request = ServerRequestFactory::fromGlobals([]);
        $response = new HtmlResponse('');

        /** @var HtmlResponse $response */
        $response = $middleware($request, $response, function (RequestInterface $request, ResponseInterface $response) {
            $response->getBody()->write('<pre>   Hello   </pre><script>  var i   =   0;</script>');
            return $response;
        });

        $body = $response->getBody();
        $body->rewind();
        
        // While the pre-element should minify, the script should not.
        $this->assertSame('<pre> Hello </pre><script>  var i   =   0;</script>', $body->getContents());
    }

    protected function _streamInterfaceCallback()
    {
        return function () {
            return new Stream(fopen('php://temp', 'r+'));
        };
    }
}