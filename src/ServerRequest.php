<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router;

/**
 * Import classes
 */
use Psr\Http\Message\ServerRequestInterface;

/**
 * ServerRequest
 *
 * @since 3.0.0
 */
final class ServerRequest implements ServerRequestInterface
{

    /**
     * @var ServerRequestInterface
     */
    private ServerRequestInterface $request;

    /**
     * Constructor of the class
     *
     * @param ServerRequestInterface $request
     */
    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * Checks if the request is JSON
     *
     * @link https://datatracker.ietf.org/doc/html/rfc4627
     *
     * @return bool
     */
    public function isJson(): bool
    {
        return $this->getContentType() === 'application/json';
    }

    /**
     * Gets the request media type
     *
     * @link https://tools.ietf.org/html/rfc7231#section-3.1.1.1
     *
     * @return string|null
     */
    public function getContentType(): ?string
    {
        if (!$this->request->hasHeader('Content-Type')) {
            return null;
        }

        $result = $this->request->getHeaderLine('Content-Type');

        if (false !== \strpos($result, ';')) {
            $result = \strstr($result, ';', true);
        }

        return \trim($result);
    }

    /**
     * {@inheritdoc}
     */
    public function getProtocolVersion(): string
    {
        return $this->request->getProtocolVersion();
    }

    /**
     * {@inheritdoc}
     */
    public function withProtocolVersion($version)
    {
        $clone = clone $this;
        $clone->request = $clone->request->withProtocolVersion($version);

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders(): array
    {
        return $this->request->getHeaders();
    }

    /**
     * {@inheritdoc}
     */
    public function hasHeader($name): bool
    {
        return $this->request->hasHeader($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getHeader($name): array
    {
        return $this->request->getHeader($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaderLine($name): string
    {
        return $this->request->getHeaderLine($name);
    }

    /**
     * {@inheritdoc}
     */
    public function withHeader($name, $value)
    {
        $clone = clone $this;
        $clone->request = $clone->request->withHeader($name, $value);

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withAddedHeader($name, $value)
    {
        $clone = clone $this;
        $clone->request = $clone->request->withAddedHeader($name, $value);

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutHeader($name)
    {
        $clone = clone $this;
        $clone->request = $clone->request->withoutHeader($name);

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getBody(): \Psr\Http\Message\StreamInterface
    {
        return $this->request->getBody();
    }

    /**
     * {@inheritdoc}
     */
    public function withBody(\Psr\Http\Message\StreamInterface $body)
    {
        $clone = clone $this;
        $clone->request = $clone->request->withBody($body);

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getMethod(): string
    {
        return $this->request->getMethod();
    }

    /**
     * {@inheritdoc}
     */
    public function withMethod($method)
    {
        $clone = clone $this;
        $clone->request = $clone->request->withMethod($method);

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getUri(): \Psr\Http\Message\UriInterface
    {
        return $this->request->getUri();
    }

    /**
     * {@inheritdoc}
     */
    public function withUri(\Psr\Http\Message\UriInterface $uri, $preserveHost = false)
    {
        $clone = clone $this;
        $clone->request = $clone->request->withUri($uri, $preserveHost);

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTarget(): string
    {
        return $this->request->getRequestTarget();
    }

    /**
     * {@inheritdoc}
     */
    public function withRequestTarget($requestTarget)
    {
        $clone = clone $this;
        $clone->request = $clone->request->withRequestTarget($requestTarget);

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getServerParams(): array
    {
        return $this->request->getServerParams();
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryParams(): array
    {
        return $this->request->getQueryParams();
    }

    /**
     * {@inheritdoc}
     */
    public function withQueryParams(array $query)
    {
        $clone = clone $this;
        $clone->request = $clone->request->withQueryParams($query);

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getCookieParams(): array
    {
        return $this->request->getCookieParams();
    }

    /**
     * {@inheritdoc}
     */
    public function withCookieParams(array $cookies)
    {
        $clone = clone $this;
        $clone->request = $clone->request->withCookieParams($cookies);

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getUploadedFiles(): array
    {
        return $this->request->getUploadedFiles();
    }

    /**
     * {@inheritdoc}
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        $clone = clone $this;
        $clone->request = $clone->request->withUploadedFiles($uploadedFiles);

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getParsedBody()
    {
        return $this->request->getParsedBody();
    }

    /**
     * {@inheritdoc}
     */
    public function withParsedBody($data)
    {
        $clone = clone $this;
        $clone->request = $clone->request->withParsedBody($data);

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes(): array
    {
        return $this->request->getAttributes();
    }

    /**
     * {@inheritdoc}
     */
    public function getAttribute($name, $default = null)
    {
        return $this->request->getAttribute($name, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function withAttribute($name, $value)
    {
        $clone = clone $this;
        $clone->request = $clone->request->withAttribute($name, $value);

        return $clone;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutAttribute($name)
    {
        $clone = clone $this;
        $clone->request = $clone->request->withoutAttribute($name);

        return $clone;
    }
}
