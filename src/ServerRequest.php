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
use Sunrise\Http\Router\Dictionary\ErrorSource;
use Sunrise\Http\Router\Entity\Language;
use Sunrise\Http\Router\Entity\MediaType;
use Sunrise\Http\Router\Exception\Http\HttpBadRequestException;
use Sunrise\Http\Router\Exception\LogicException;
use Sunrise\Http\Router\Helper\HeaderParser;

use function current;
use function sprintf;
use function usort;

/**
 * Server Request Proxy
 *
 * @since 3.0.0
 */
final class ServerRequest implements ServerRequestInterface
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
     * Gets the original request
     *
     * @return ServerRequestInterface
     */
    public function getOriginalRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    /**
     * Gets a media type that the client produced
     *
     * @return MediaType|null
     *
     * @throws HttpBadRequestException If the "Content-Type" header isn't valid.
     */
    public function getClientProducedMediaType(): ?MediaType
    {
        $header = $this->request->getHeaderLine('Content-Type');

        try {
            return HeaderParser::parseAcceptHeader($header, allowRange: false)->current();
        } catch (\InvalidArgumentException $e) {
            $message = sprintf('The "Content-Type" header is invalid: %s', $e->getMessage());

            throw (new HttpBadRequestException($message, previous: $e))
                ->setSource(ErrorSource::CLIENT_REQUEST_HEADER);
        }
    }

    /**
     * Gets media types that the client consumes
     *
     * @return Generator<int, MediaType>
     *
     * @throws HttpBadRequestException If the "Accept" header isn't valid.
     */
    public function getClientConsumesMediaTypes(): Generator
    {
        $header = $this->request->getHeaderLine('Accept');

        try {
            yield from HeaderParser::parseAcceptHeader($header);
        } catch (\InvalidArgumentException $e) {
            $message = sprintf('The "Accept" header is invalid: %s', $e->getMessage());

            throw (new HttpBadRequestException($message, previous: $e))
                ->setSource(ErrorSource::CLIENT_REQUEST_HEADER);
        }
    }

    /**
     * Gets languages that the client consumes
     *
     * @return Generator<int, Language>
     *
     * @throws HttpBadRequestException If the "Accept-Language" header isn't valid.
     */
    public function getClientConsumesLanguages(): Generator
    {
        $header = $this->request->getHeaderLine('Accept-Language');

        try {
            yield from HeaderParser::parseAcceptLanguageHeader($header);
        } catch (\InvalidArgumentException $e) {
            $message = sprintf('The "Accept-Language" header is invalid: %s', $e->getMessage());

            throw (new HttpBadRequestException($message, previous: $e))
                ->setSource(ErrorSource::CLIENT_REQUEST_HEADER);
        }
    }

    /**
     * Gets the client's preferred media type
     *
     * @param MediaType ...$serverProducesMediaTypes
     *
     * @return MediaType
     *
     * @throws LogicException If the list of produces media types is empty.
     */
    public function getClientPreferredMediaType(MediaType ...$serverProducesMediaTypes): MediaType
    {
        if ($serverProducesMediaTypes === []) {
            throw new LogicException('The media types that the server produces must be provided.');
        }

        /**
         * @var list<MediaType> $clientConsumesMediaTypes
         * @psalm-suppress UnnecessaryVarAnnotation
         */
        $clientConsumesMediaTypes = [...$this->getClientConsumesMediaTypes()];
        if ($clientConsumesMediaTypes === []) {
            return current($serverProducesMediaTypes);
        }

        usort($clientConsumesMediaTypes, static fn(MediaType $a, MediaType $b): int => (
            $b->getParameter('q', '1.0') <=> $a->getParameter('q', '1.0')
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
     * Gets the client's preferred language
     *
     * @param Language ...$serverProducesLanguages
     *
     * @return Language
     *
     * @throws LogicException If the list of produces languages is empty.
     */
    public function getClientPreferredLanguage(Language ...$serverProducesLanguages): Language
    {
        if ($serverProducesLanguages === []) {
            throw new LogicException('The languages that the server produces must be provided.');
        }

        /**
         * @var list<Language> $clientConsumesLanguages
         * @psalm-suppress UnnecessaryVarAnnotation
         */
        $clientConsumesLanguages = [...$this->getClientConsumesLanguages()];
        if ($clientConsumesLanguages === []) {
            return current($serverProducesLanguages);
        }

        usort($clientConsumesLanguages, static fn(Language $a, Language $b): int => (
            $b->getParameter('q', '1.0') <=> $a->getParameter('q', '1.0')
        ));

        foreach ($clientConsumesLanguages as $clientConsumesLanguage) {
            foreach ($serverProducesLanguages as $serverProducesLanguage) {
                if ($serverProducesLanguage->equals($clientConsumesLanguage)) {
                    return $serverProducesLanguage;
                }
            }
        }

        return current($serverProducesLanguages);
    }

    /**
     * Checks if the client produces one of the given media types
     *
     * @param MediaType ...$serverConsumesMediaTypes
     *
     * @return bool
     *
     * @throws LogicException If the media types that the server consumes were not provided.
     */
    public function clientProducesMediaType(MediaType ...$serverConsumesMediaTypes): bool
    {
        if ($serverConsumesMediaTypes === []) {
            throw new LogicException('The media types that the server consumes must be provided.');
        }

        $clientProducedMediaType = $this->getClientProducedMediaType();
        if ($clientProducedMediaType === null) {
            return false;
        }

        foreach ($serverConsumesMediaTypes as $serverConsumesMediaType) {
            if ($clientProducedMediaType->equals($serverConsumesMediaType)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if the client consumes one of the given media types
     *
     * @param MediaType ...$serverProducesMediaTypes
     *
     * @return bool
     *
     * @throws LogicException If the media types that the server produces were not provided.
     */
    public function clientConsumesMediaType(MediaType ...$serverProducesMediaTypes): bool
    {
        if ($serverProducesMediaTypes === []) {
            throw new LogicException('The media types that the server produces must be provided.');
        }

        $clientConsumesMediaTypes = $this->getClientConsumesMediaTypes();
        foreach ($clientConsumesMediaTypes as $clientConsumesMediaType) {
            foreach ($serverProducesMediaTypes as $serverProducesMediaType) {
                if ($serverProducesMediaType->equals($clientConsumesMediaType)) {
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
