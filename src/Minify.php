<?php
/**
 * PSR7 Minify Middleware.
 *
 * @license https://github.com/adriansuter/psr7-minify-middleware/blob/master/LICENSE (MIT License)
 */

namespace AdrianSuter\PSR7\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Class Minify.
 *
 * @package AdrianSuter\PSR7\Middleware
 */
class Minify
{

    /**
     * @var callable
     */
    private $_streamInterfaceCallback = null;

    /**
     * @var array
     */
    private $_ignoreTags = ['textarea', 'pre'];

    /**
     * Constructor.
     *
     * @param callable $streamInterfaceCallback The callback should return a new object implementing the StreamInterface.
     * @param array $ignoreTags The tags (html elements) to be skipped from the minification.
     */
    public function __construct(callable $streamInterfaceCallback, $ignoreTags = ['textarea', 'pre'])
    {
        $this->_streamInterfaceCallback = $streamInterfaceCallback;
        $this->_ignoreTags = $ignoreTags;
    }

    /**
     * Invokes this middleware.
     *
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
        if (false !== stripos(implode('', $contentType), 'text/html')) {
            $body = $response->getBody();
            if ($body->isSeekable()) {
                $body->rewind();

                $content = $this->_minifyHtml($body->getContents());

                /** @var StreamInterface $body */
                $body = call_user_func($this->_streamInterfaceCallback);
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
     *
     * @see http://stackoverflow.com/a/27990578
     */
    protected function _minifyHtml($buffer)
    {
        // Count the number of html elements to be ignored.
        $c_ignoreTags = count($this->_ignoreTags);

        // Find all html elements to be ignored in the given html code.
        $patterns = [];
        for ($i = 0; $i < $c_ignoreTags; $i++) {
            preg_match_all('#\<' . $this->_ignoreTags[$i] . '.*\>.*\<\/' . $this->_ignoreTags[$i] . '\>#Uis', $buffer,
                $patterns[$i]);
        }

        // Replace all the html elements to be ignored by special placeholders.
        for ($i = 0; $i < $c_ignoreTags; $i++) {
            $ignoreTag = $this->_ignoreTags[$i];
            $buffer = str_replace($patterns[$i][0], array_map(function ($el) use ($ignoreTag) {
                return '<' . $ignoreTag . '>' . $el . '</' . $ignoreTag . '>';
            }, array_keys($patterns[$i][0])), $buffer);
        }

        ///
        // Minify the html code.
        ///
        // See: https://github.com/christianklisch/slim-minify
        //      https://github.com/christianklisch/slim-minify/blob/master/src/Slim/Middleware/Minify.php
        $search = [
            '/(?:(?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:(?<!\:|\\\|\')\/\/.*))/',
            '/\n/',
            '/\>[^\S ]+/s',
            '/[^\S ]+\</s',
            '/(\s)+/s'
        ];
        $replace = [' ', ' ', '>', '<', '\\1'];
        $buffer = preg_replace($search, $replace, $buffer);

        // Restore the html elements to be ignored.
        for ($i = 0; $i < $c_ignoreTags; $i++) {
            $ignoreTag = $this->_ignoreTags[$i];
            $buffer = str_replace(array_map(function ($el) use ($ignoreTag) {
                return '<' . $ignoreTag . '>' . $el . '</' . $ignoreTag . '>';
            }, array_keys($patterns[$i][0])), $patterns[$i][0], $buffer);
        }

        return $buffer;
    }

}
