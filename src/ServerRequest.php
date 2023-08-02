<?php

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

declare(strict_types=1);

namespace Sunrise\Http\Router;

use Fig\Http\Message\RequestMethodInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Sunrise\Http\Router\Entity\IpAddress;

use Sunrise\Http\Router\Entity\MediaType;
use function array_merge;
use function explode;
use function key;
use function preg_split;
use function reset;
use function strncmp;
use function strpos;
use function strstr;
use function strtolower;
use function trim;

/**
 * ServerRequest
 *
 * @since 3.0.0
 */
final class ServerRequest implements ServerRequestInterface, RequestMethodInterface
{

    /**
     * Constructor of the class
     *
     * @param ServerRequestInterface $request
     */
    public function __construct(private ServerRequestInterface $request)
    {
    }

    /**
     * Creates the proxy from the given request
     *
     * @param ServerRequestInterface $request
     *
     * @return self
     */
    public static function from(ServerRequestInterface $request): self
    {
        if ($request instanceof self) {
            return $request;
        }

        return new self($request);
    }

    /**
     * Checks if the request is JSON
     *
     * @link https://tools.ietf.org/html/rfc4627
     *
     * @return bool
     */
    public function isJson(): bool
    {
        return $this->clientProducesMediaType([
            MediaType::APPLICATION_JSON,
        ]);
    }

    /**
     * Checks if the request is XML
     *
     * @link https://tools.ietf.org/html/rfc2376
     *
     * @return bool
     */
    public function isXml(): bool
    {
        return $this->clientProducesMediaType([
            MediaType::APPLICATION_XML,
            MediaType::TEXT_XML,
        ]);
    }

    /**
     * Gets the client's IP address
     *
     * @param array<non-empty-string, non-empty-string> $proxyChain
     *
     * @return IpAddress
     */
    public function getClientIpAddress(array $proxyChain = []): IpAddress
    {
        $serverParams = $this->request->getServerParams();

        /** @var non-empty-string $clientAddress */
        $clientAddress = $serverParams['REMOTE_ADDR'] ?? '::1';

        /** @var list<non-empty-string> $proxyAddresses */
        $proxyAddresses = [];

        while (isset($proxyChain[$clientAddress])) {
            $proxyHeader = $proxyChain[$clientAddress];
            unset($proxyChain[$clientAddress]);

            $header = $this->request->getHeaderLine($proxyHeader);
            if ($header === '') {
                break;
            }

            /** @var list<non-empty-string> $addresses */
            $addresses = preg_split('/\s*,\s*/', $header, -1, PREG_SPLIT_NO_EMPTY);
            if ($addresses === []) {
                break;
            }

            $clientAddress = array_shift($addresses);
            if ($addresses === []) {
                continue;
            }

            $proxyAddresses = array_merge($proxyAddresses, $addresses);
        }

        return new IpAddress($clientAddress, $proxyAddresses);
    }

    /**
     * Gets the client's produced media type
     *
     * @link https://tools.ietf.org/html/rfc7231#section-3.1.1.1
     * @link https://tools.ietf.org/html/rfc7231#section-3.1.1.5
     *
     * @return string
     */
    public function getClientProducedMediaType(): string
    {
        $header = $this->request->getHeaderLine('Content-Type');
        if ($header === '') {
            return '';
        }

        $result = strstr($header, ';', true);
        if ($result === false) {
            $result = $header;
        }

        $result = trim($result);
        if ($result === '') {
            return '';
        }

        return strtolower($result);
    }

    /**
     * Gets the client's consumed media types
     *
     * @link https://tools.ietf.org/html/rfc7231#section-1.2
     * @link https://tools.ietf.org/html/rfc7231#section-3.1.1.1
     * @link https://tools.ietf.org/html/rfc7231#section-5.3.2
     *
     * @return array<lowercase-string, array<string, string>>
     */
    public function getClientConsumedMediaTypes(): array
    {
        $header = $this->request->getHeaderLine('Accept');
        if ($header === '') {
            return [];
        }

        $accepts = parse_accept_header($header);
        if (empty($accepts)) {
            return [];
        }

        $result = [];
        foreach ($accepts as $type => $params) {
            $result[strtolower($type)] = $params;
        }

        return $result;
    }

    /**
     * Gets the client's consumed encodings
     *
     * @return array<string, array<string, string>>
     */
    public function getClientConsumedEncodings(): array
    {
        $header = $this->request->getHeaderLine('Accept-Encoding');
        if ($header === '') {
            return [];
        }

        $accepts = header_accept_like_parse($header);
        if (empty($accepts)) {
            return [];
        }

        return $accepts;
    }

    /**
     * Gets the client's consumed languages
     *
     * @return array<string, array<string, string>>
     */
    public function getClientConsumedLanguages(): array
    {
        $header = $this->request->getHeaderLine('Accept-Language');
        if ($header === '') {
            return [];
        }

        $accepts = header_accept_like_parse($header);
        if (empty($accepts)) {
            return [];
        }

        return $accepts;
    }

    /**
     * Checks if the client produces one of the given media types
     *
     * @param list<string> $consumes
     *
     * @return bool
     */
    public function clientProducesMediaType(array $consumes): bool
    {
        if ($consumes === []) {
            return true;
        }

        $produced = $this->getClientProducedMediaType();
        if ($produced === '') {
            return false;
        }

        foreach ($consumes as $consumed) {
            if (media_types_compare($consumed, $produced)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if the client consumes one of the given media types
     *
     * @param list<string> $produces
     *
     * @return bool
     */
    public function clientConsumesMediaType(array $produces): bool
    {
        if ($produces === []) {
            return true;
        }

        $consumes = $this->getClientConsumedMediaTypes();
        if ($consumes === []) {
            return true;
        }

        if (isset($consumes['*/*'])) {
            return true;
        }

        foreach ($produces as $a) {
            foreach ($consumes as $b => $_) {
                if (media_types_compare($a, $b)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Checks if the given media types are equal
     *
     * @param string $a
     * @param string $b
     *
     * @return bool
     */
    public function equalsMediaTypes(string $a, string $b): bool
    {
        if ($a === $b) {
            return true;
        }

        $slash = strpos($a, '/');
        if ($slash === false || !isset($b[$slash]) || $b[$slash] !== '/') {
            return false;
        }

        $star = $slash + 1;
        if (!isset($a[$star], $b[$star])) {
            return false;
        }

        if (!($a[$star] === '*' || $b[$star] === '*')) {
            return false;
        }

        return strncmp($a, $b, $star) === 0;
    }

    /**
     * @inheritDoc
     */
    public function getProtocolVersion(): string
    {
        return $this->request->getProtocolVersion();
    }

    /**
     * @inheritDoc
     */
    public function withProtocolVersion($version): self
    {
        $clone = clone $this;
        $clone->request = $clone->request->withProtocolVersion($version);

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getHeaders(): array
    {
        return $this->request->getHeaders();
    }

    /**
     * @inheritDoc
     */
    public function hasHeader($name): bool
    {
        return $this->request->hasHeader($name);
    }

    /**
     * @inheritDoc
     */
    public function getHeader($name): array
    {
        return $this->request->getHeader($name);
    }

    /**
     * @inheritDoc
     */
    public function getHeaderLine($name): string
    {
        return $this->request->getHeaderLine($name);
    }

    /**
     * @inheritDoc
     */
    public function withHeader($name, $value): self
    {
        $clone = clone $this;
        $clone->request = $clone->request->withHeader($name, $value);

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function withAddedHeader($name, $value): self
    {
        $clone = clone $this;
        $clone->request = $clone->request->withAddedHeader($name, $value);

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function withoutHeader($name): self
    {
        $clone = clone $this;
        $clone->request = $clone->request->withoutHeader($name);

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getBody(): StreamInterface
    {
        return $this->request->getBody();
    }

    /**
     * @inheritDoc
     */
    public function withBody(StreamInterface $body): self
    {
        $clone = clone $this;
        $clone->request = $clone->request->withBody($body);

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getMethod(): string
    {
        return $this->request->getMethod();
    }

    /**
     * @inheritDoc
     */
    public function withMethod($method): self
    {
        $clone = clone $this;
        $clone->request = $clone->request->withMethod($method);

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getUri(): UriInterface
    {
        return $this->request->getUri();
    }

    /**
     * @inheritDoc
     */
    public function withUri(UriInterface $uri, $preserveHost = false): self
    {
        $clone = clone $this;
        $clone->request = $clone->request->withUri($uri, $preserveHost);

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getRequestTarget(): string
    {
        return $this->request->getRequestTarget();
    }

    /**
     * @inheritDoc
     */
    public function withRequestTarget($requestTarget): self
    {
        $clone = clone $this;
        $clone->request = $clone->request->withRequestTarget($requestTarget);

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getServerParams(): array
    {
        return $this->request->getServerParams();
    }

    /**
     * @inheritDoc
     */
    public function getQueryParams(): array
    {
        return $this->request->getQueryParams();
    }

    /**
     * @inheritDoc
     */
    public function withQueryParams(array $query): self
    {
        $clone = clone $this;
        $clone->request = $clone->request->withQueryParams($query);

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getCookieParams(): array
    {
        return $this->request->getCookieParams();
    }

    /**
     * @inheritDoc
     */
    public function withCookieParams(array $cookies): self
    {
        $clone = clone $this;
        $clone->request = $clone->request->withCookieParams($cookies);

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getUploadedFiles(): array
    {
        return $this->request->getUploadedFiles();
    }

    /**
     * @inheritDoc
     */
    public function withUploadedFiles(array $uploadedFiles): self
    {
        $clone = clone $this;
        $clone->request = $clone->request->withUploadedFiles($uploadedFiles);

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getParsedBody(): mixed
    {
        return $this->request->getParsedBody();
    }

    /**
     * @inheritDoc
     */
    public function withParsedBody($data): self
    {
        $clone = clone $this;
        $clone->request = $clone->request->withParsedBody($data);

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getAttributes(): array
    {
        return $this->request->getAttributes();
    }

    /**
     * @inheritDoc
     */
    public function getAttribute($name, $default = null): mixed
    {
        return $this->request->getAttribute($name, $default);
    }

    /**
     * @inheritDoc
     */
    public function withAttribute($name, $value): self
    {
        $clone = clone $this;
        $clone->request = $clone->request->withAttribute($name, $value);

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function withoutAttribute($name): self
    {
        $clone = clone $this;
        $clone->request = $clone->request->withoutAttribute($name);

        return $clone;
    }
}
