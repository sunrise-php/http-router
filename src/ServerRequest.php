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
use Generator;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Sunrise\Http\Router\Entity\ClientRemoteAddress;
use Sunrise\Http\Router\Entity\MediaType;
use Sunrise\Http\Router\Exception\Http\HttpBadRequestException;
use Sunrise\Http\Router\Exception\InvalidArgumentException;

use Throwable;
use function current;
use function explode;
use function key;
use function preg_split;
use function sprintf;
use function strncmp;
use function strpos;
use function strtolower;
use function usort;

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
     * Gets the client's remote address
     *
     * @param array<TKey, TValue> $proxyChain
     *
     * @return ClientRemoteAddress
     *
     * @template TKey as non-empty-string Proxy address; e.g., 127.0.0.1
     * @template TValue as non-empty-string Trusted header; e.g., X-Forwarded-For
     *
     * @link https://www.rfc-editor.org/rfc/rfc7239.html#section-5.2
     */
    public function getClientRemoteAddress(array $proxyChain = []): ClientRemoteAddress
    {
        $serverParams = $this->request->getServerParams();
        /** @var non-empty-string $clientAddress */
        $clientAddress = $serverParams['REMOTE_ADDR'] ?? '::1';
        /** @var list<non-empty-string> $proxyAddresses */
        $proxyAddresses = [];

        while (isset($proxyChain[$clientAddress])) {
            $trustedHeader = $proxyChain[$clientAddress];
            unset($proxyChain[$clientAddress]);

            $header = $this->request->getHeaderLine($trustedHeader);
            if ($header === '') {
                break;
            }

            /** @var list<non-empty-string> $addresses */
            $addresses = preg_split('/\s*,\s*/', $header, -1, PREG_SPLIT_NO_EMPTY);
            if (empty($addresses)) {
                break;
            }

            $clientAddress = $addresses[0];
            unset($addresses[0]);

            foreach ($addresses as $address) {
                $proxyAddresses[] = $address;
            }
        }

        return new ClientRemoteAddress($clientAddress, $proxyAddresses);
    }

    /**
     * Gets the media type that the client produced
     *
     * @return MediaType|null
     */
    public function getClientProducedMediaType(): ?MediaType
    {
        $header = $this->request->getHeaderLine('Content-Type');

        try {
            return parse_header_with_media_type($header)->current();
        } catch (InvalidArgumentException $e) {
            throw new HttpBadRequestException(sprintf('The Content-Type header is invalid: %s', $e->getMessage()));
        }
    }

    /**
     * Gets the media types that the client consumes
     *
     * @return Generator<int<0, max>, MediaType>
     */
    public function getClientConsumesMediaTypes(): Generator
    {
        $header = $this->request->getHeaderLine('Accept');

        try {
            yield from parse_header_with_media_type($header);
        } catch (InvalidArgumentException $e) {
            throw new HttpBadRequestException(sprintf('The Accept header is invalid: %s', $e->getMessage()));
        }
    }

    /**
     * Gets the client's preferred media type
     *
     * @param MediaType ...$serverProducesMediaTypes
     *
     * @return MediaType|null
     */
    public function getClientPreferredMediaType(MediaType ...$serverProducesMediaTypes): ?MediaType
    {
        if ($serverProducesMediaTypes === []) {
            return null;
        }

        /** @var list<MediaType> $clientConsumesMediaTypes */
        $clientConsumesMediaTypes = [...$this->getClientConsumesMediaTypes()];

        if ($clientConsumesMediaTypes === []) {
            return current($serverProducesMediaTypes);
        }

        usort($clientConsumesMediaTypes, static fn (MediaType $a, MediaType $b): int => (
            $b->getQualityFactor() <=> $a->getQualityFactor()
        ));

        foreach ($clientConsumesMediaTypes as $clientConsumesMediaType) {
            foreach ($serverProducesMediaTypes as $serverProducesMediaType) {
                if ($serverProducesMediaType->equals($clientConsumesMediaType)) {
                    return $serverProducesMediaType;
                }
            }
        }

        return current($serverProducesMediaTypes);
    }

    /**
     * Checks if the client produces one of the given media types
     *
     * @param MediaType ...$consumes
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
