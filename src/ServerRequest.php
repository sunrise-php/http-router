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
use Sunrise\Http\Router\Entity\IpAddress;

/**
 * Import functions
 */
use function explode;
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
     * Creates the class from the given request
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
            'application/json',
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
            'application/xml',
            'text/xml',
        ]);
    }

    /**
     * Checks if the request is XMLHttpRequest
     *
     * @return bool
     */
    public function isXmlHttpRequest(): bool
    {
        return $this->request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest';
    }

    /**
     * Gets the client's IP address
     *
     * @param array<string, string> $proxyChain
     *
     * @return IpAddress
     */
    public function getClientIpAddress(array $proxyChain = []): IpAddress
    {
        $env = $this->request->getServerParams();

        /** @var string */
        $clientIp = $env['REMOTE_ADDR'] ?? '::1';

        while (isset($proxyChain[$clientIp])) {
            $trustedHeader = $proxyChain[$clientIp];
            unset($proxyChain[$clientIp]);

            $header = $this->request->getHeaderLine($trustedHeader);
            if ($header === '') {
                break;
            }

            $proxiedClientIp = strstr($header, ',', true);
            if ($proxiedClientIp === false) {
                $proxiedClientIp = $header;
            }

            $proxiedClientIp = trim($proxiedClientIp);
            if ($proxiedClientIp === '') {
                break;
            }

            $clientIp = $proxiedClientIp;
        }

        return new IpAddress($clientIp);
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

        $mediaType = strstr($header, ';', true);
        if ($mediaType === false) {
            $mediaType = $header;
        }

        $mediaType = trim($mediaType);
        if ($mediaType === '') {
            return '';
        }

        return strtolower($mediaType);
    }

    /**
     * Gets the client's consumed media types
     *
     * @link https://tools.ietf.org/html/rfc7231#section-1.2
     * @link https://tools.ietf.org/html/rfc7231#section-3.1.1.1
     * @link https://tools.ietf.org/html/rfc7231#section-5.3.2
     *
     * @return list<string>
     */
    public function getClientConsumedMediaTypes(): array
    {
        $header = $this->request->getHeaderLine('Accept');
        if ($header === '') {
            return [];
        }

        $result = [];
        $accepts = explode(',', $header);
        foreach ($accepts as $accept) {
            $mediaType = strstr($accept, ';', true);
            if ($mediaType === false) {
                $mediaType = $accept;
            }

            $mediaType = trim($mediaType);
            if ($mediaType === '') {
                continue;
            }

            if ($mediaType === '*/*') {
                return [];
            }

            $result[] = strtolower($mediaType);
        }

        return $result;
    }

    /**
     * Gets the client's consumed languages
     *
     * @return array<string, numeric>
     */
    public function getClientConsumedLanguages(): array
    {
        $header = $this->request->getHeaderLine('Accept-Language');
        if ($header === '') {
            return [];
        }

        $cursor = -1;
        $inLanguage = true;
        $inWeight = false;
        $rows = [];
        $i = 0;

        while (true) {
            $char = $header[++$cursor] ?? null;

            if ($char === null) {
                break;
            }
            if ($char === ' ') {
                continue;
            }
            if ($char === ';') {
                $inLanguage = false;
                continue;
            }
            if ($char === '=') {
                $inWeight = true;
                continue;
            }
            if ($char === ',') {
                $inLanguage = true;
                $inWeight = false;
                $i++;
                continue;
            }
            if ($inLanguage) {
                $rows[$i][0] ??= '';
                $rows[$i][0] .= $char;
                continue;
            }
            if ($inWeight) {
                $rows[$i][1] ??= '';
                $rows[$i][1] .= $char;
                continue;
            }
        }

        $result = [];
        foreach ($rows as $row) {
            $result[$row[0]] = $row[1] ?? '1';
        }

        return $result;
    }

    /**
     * Checks if the client produces one of the given media types
     *
     * @param list<string> $consumedMediaTypes
     *
     * @return bool
     */
    public function clientProducesMediaType(array $consumedMediaTypes): bool
    {
        if ($consumedMediaTypes === []) {
            return true;
        }

        $producedMediaType = $this->getClientProducedMediaType();
        if ($producedMediaType === '') {
            return false;
        }

        foreach ($consumedMediaTypes as $consumedMediaType) {
            if ($this->equalsMediaTypes($consumedMediaType, $producedMediaType)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if the client consumes one of the given media types
     *
     * @param list<string> $producedMediaTypes
     *
     * @return bool
     */
    public function clientConsumesMediaType(array $producedMediaTypes): bool
    {
        if ($producedMediaTypes === []) {
            return true;
        }

        $consumedMediaTypes = $this->getClientConsumedMediaTypes();
        if ($consumedMediaTypes === []) {
            return true;
        }

        foreach ($producedMediaTypes as $a) {
            foreach ($consumedMediaTypes as $b) {
                if ($this->equalsMediaTypes($a, $b)) {
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
        if ($slash === false) {
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