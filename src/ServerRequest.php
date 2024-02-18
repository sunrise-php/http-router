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

use Generator;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Sunrise\Http\Router\Entity\Encoding;
use Sunrise\Http\Router\Entity\Language;
use Sunrise\Http\Router\Entity\LanguageComparator;
use Sunrise\Http\Router\Entity\LanguageInterface;
use Sunrise\Http\Router\Entity\MediaType;
use Sunrise\Http\Router\Entity\MediaTypeComparator;
use Sunrise\Http\Router\Entity\MediaTypeInterface;
use Sunrise\Http\Router\Helper\HeaderParser;

/**
 * @since 3.0.0
 */
final class ServerRequest implements ServerRequestInterface
{
    private ?LanguageComparator $languageComparator = null;
    private ?MediaTypeComparator $mediaTypeComparator = null;

    public function __construct(private ServerRequestInterface $request)
    {
    }

    public static function create(ServerRequestInterface $request): self
    {
        return ($request instanceof self) ? $request : new self($request);
    }

    public function getOriginalRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    public function getLanguageComparator(): LanguageComparator
    {
        return $this->languageComparator ??= new LanguageComparator();
    }

    public function getMediaTypeComparator(): MediaTypeComparator
    {
        return $this->mediaTypeComparator ??= new MediaTypeComparator();
    }

    public function getClientProducedMediaType(): ?MediaType
    {
        return HeaderParser::parseContentTypeHeader($this->request->getHeaderLine('Content-Type'));
    }

    /**
     * @return Generator<int<0, max>, MediaType>
     */
    public function getClientConsumedMediaTypes(): Generator
    {
        yield from HeaderParser::parseAcceptHeader($this->request->getHeaderLine('Accept'));
    }

    /**
     * @return Generator<int<0, max>, Encoding>
     */
    public function getClientConsumedEncodings(): Generator
    {
        yield from HeaderParser::parseAcceptEncodingHeader($this->request->getHeaderLine('Accept-Encoding'));
    }

    /**
     * @return Generator<int<0, max>, Language>
     */
    public function getClientConsumedLanguages(): Generator
    {
        yield from HeaderParser::parseAcceptLanguageHeader($this->request->getHeaderLine('Accept-Language'));
    }

    public function getClientPreferredMediaType(MediaTypeInterface ...$serverProducedMediaTypes): ?MediaType
    {
        if ($serverProducedMediaTypes === []) {
            return null;
        }

        $mediaTypeComparator = $this->getMediaTypeComparator();
        foreach ($this->getClientConsumedMediaTypes() as $clientConsumedMediaType) {
            foreach ($serverProducedMediaTypes as $serverProducedMediaType) {
                if ($mediaTypeComparator->equals($clientConsumedMediaType, $serverProducedMediaType)) {
                    return $clientConsumedMediaType;
                }
            }
        }

        return null;
    }

    public function getClientPreferredLanguage(LanguageInterface ...$serverProducedLanguages): ?Language
    {
        if ($serverProducedLanguages === []) {
            return null;
        }

        $languageComparator = $this->getLanguageComparator();
        foreach ($this->getClientConsumedLanguages() as $clientConsumedLanguage) {
            foreach ($serverProducedLanguages as $serverProducedLanguage) {
                if ($languageComparator->equals($clientConsumedLanguage, $serverProducedLanguage)) {
                    return $clientConsumedLanguage;
                }
            }
        }

        return null;
    }

    public function clientProducesMediaType(MediaTypeInterface ...$serverConsumedMediaTypes): bool
    {
        // This case is interpreted as follows: the server accepts any media type...
        if ($serverConsumedMediaTypes === []) {
            return true;
        }

        $clientProducedMediaType = $this->getClientProducedMediaType();
        if ($clientProducedMediaType === null) {
            return false;
        }

        $mediaTypeComparator = $this->getMediaTypeComparator();
        foreach ($serverConsumedMediaTypes as $serverConsumedMediaType) {
            if ($mediaTypeComparator->equals($clientProducedMediaType, $serverConsumedMediaType)) {
                return true;
            }
        }

        return false;
    }

    public function clientConsumesMediaType(MediaTypeInterface ...$serverProducedMediaTypes): bool
    {
        if ($serverProducedMediaTypes === []) {
            return false;
        }

        $mediaTypeComparator = $this->getMediaTypeComparator();
        foreach ($this->getClientConsumedMediaTypes() as $clientConsumedMediaType) {
            foreach ($serverProducedMediaTypes as $serverProducedMediaType) {
                if ($mediaTypeComparator->equals($clientConsumedMediaType, $serverProducedMediaType)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function clientConsumesLanguage(LanguageInterface ...$serverProducedLanguages): bool
    {
        if ($serverProducedLanguages === []) {
            return false;
        }

        $languageComparator = $this->getLanguageComparator();
        foreach ($this->getClientConsumedLanguages() as $clientConsumedLanguage) {
            foreach ($serverProducedLanguages as $serverProducedLanguage) {
                if ($languageComparator->equals($clientConsumedLanguage, $serverProducedLanguage)) {
                    return true;
                }
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
