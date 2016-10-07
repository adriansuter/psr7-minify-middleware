<?php

namespace AdrianSuter\PSR7\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Stream;

/**
 * Class Minify.
 *
 * @package AdrianSuter\PSR7\Middleware
 */
class Minify
{

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable|null $next
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        if ($next) {
            $response = $next($request, $response);
        }

        // If the content type is text/html, we would minify the code.
        $contentType = $response->getHeader('Content-type');
        if (FALSE !== stripos(implode('', $contentType), 'text/html')) {
            $body = $response->getBody();
            if ($body->isSeekable()) {
                $body->rewind();

                $content = $this->_minifyHtml($body->getContents());

                $body = new Stream(fopen('php://temp', 'r+'));
                $body->write($content);
                $response = $response->withBody($body);
            }
        }


        return $response;
    }

    /**
     * Minifies the given html code.
     *
     * @param string $buffer The html code.
     * @return string The minified html code.
     * @see http://stackoverflow.com/a/27990578
     */
    private function _minifyHtml($buffer)
    {
        // Searching textarea and pre
        preg_match_all('#\<textarea.*\>.*\<\/textarea\>#Uis', $buffer, $foundTxt);
        preg_match_all('#\<pre.*\>.*\<\/pre\>#Uis', $buffer, $foundPre);

        // replacing both with <textarea>$index</textarea> / <pre>$index</pre>
        $buffer = str_replace($foundTxt[0], array_map(function ($el) {
            return '<textarea>' . $el . '</textarea>';
        }, array_keys($foundTxt[0])), $buffer);
        $buffer = str_replace($foundPre[0], array_map(function ($el) {
            return '<pre>' . $el . '</pre>';
        }, array_keys($foundPre[0])), $buffer);

        // See: https://github.com/christianklisch/slim-minify
        //      https://github.com/christianklisch/slim-minify/blob/master/src/Slim/Middleware/Minify.php
        $search = ['/(?:(?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:(?<!\:|\\\|\')\/\/.*))/', '/\n/', '/\>[^\S ]+/s', '/[^\S ]+\</s', '/(\s)+/s'];
        $replace = [' ', ' ', '>', '<', '\\1'];
        $buffer = preg_replace($search, $replace, $buffer);

        // Replacing back with content
        $buffer = str_replace(array_map(function ($el) {
            return '<textarea>' . $el . '</textarea>';
        }, array_keys($foundTxt[0])), $foundTxt[0], $buffer);
        $buffer = str_replace(array_map(function ($el) {
            return '<pre>' . $el . '</pre>';
        }, array_keys($foundPre[0])), $foundPre[0], $buffer);

        return $buffer;
    }

}