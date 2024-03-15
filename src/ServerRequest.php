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
use LogicException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Sunrise\Http\Router\Entity\Language\ClientLanguage;
use Sunrise\Http\Router\Entity\Language\LanguageComparator;
use Sunrise\Http\Router\Entity\Language\LanguageComparatorInterface;
use Sunrise\Http\Router\Entity\Language\LanguageInterface;
use Sunrise\Http\Router\Entity\MediaType\ClientMediaType;
use Sunrise\Http\Router\Entity\MediaType\MediaTypeComparator;
use Sunrise\Http\Router\Entity\MediaType\MediaTypeComparatorInterface;
use Sunrise\Http\Router\Entity\MediaType\MediaTypeInterface;
use Sunrise\Http\Router\Helper\HeaderParser;

use function current;
use function preg_match;
use function usort;

/**
 * @since 3.0.0
 */
final class ServerRequest implements ServerRequestInterface
{
    private ?LanguageComparatorInterface $languageComparator = null;

    private ?MediaTypeComparatorInterface $mediaTypeComparator = null;

    public function __construct(private ServerRequestInterface $request)
    {
    }

    public static function create(ServerRequestInterface $request): self
    {
        return $request instanceof self ? $request : new self($request);
    }

    public function getOriginalRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    public function getLanguageComparator(): LanguageComparatorInterface
    {
        return $this->languageComparator ??= new LanguageComparator();
    }

    public function withLanguageComparator(LanguageComparatorInterface $languageComparator): self
    {
        $clone = clone $this;
        $clone->languageComparator = $languageComparator;

        return $clone;
    }

    public function getMediaTypeComparator(): MediaTypeComparatorInterface
    {
        return $this->mediaTypeComparator ??= new MediaTypeComparator();
    }

    public function withMediaTypeComparator(MediaTypeComparatorInterface $mediaTypeComparator): self
    {
        $clone = clone $this;
        $clone->mediaTypeComparator = $mediaTypeComparator;

        return $clone;
    }

    /**
     * @throws LogicException If the request doesn't contain a route.
     */
    public function getRoute(): RouteInterface
    {
        $route = $this->request->getAttribute('@route');
        if (! $route instanceof RouteInterface) {
            throw new LogicException('At this level of the application, the request does not contain information about the requested route.');
        }

        return $route;
    }

    public function getClientProducedMediaType(): ?ClientMediaType
    {
        $header = $this->request->getHeaderLine('Content-Type');
        if ($header === '') {
            return null;
        }

        $values = HeaderParser::parseHeader($header);
        if ($values === []) {
            return null;
        }

        [$identifier, $parameters] = current($values);

        if (preg_match('|^([^/*]+)/([^/*]+)$|', $identifier, $matches)) {
            return new ClientMediaType($matches[1], $matches[2], $parameters);
        }

        return null;
    }

    /**
     * @return Generator<int<0, max>, ClientMediaType>
     */
    public function getClientConsumedMediaTypes(): Generator
    {
        $header = $this->request->getHeaderLine('Accept');
        if ($header === '') {
            return;
        }

        $values = HeaderParser::parseHeader($header);
        if ($values === []) {
            return;
        }

        // https://github.com/php/php-src/blob/d9549d2ee215cb04aa5d2e3195c608d581fb272c/ext/standard/array.c#L900-L903
        usort($values, static fn(array $a, array $b): int => (
            (float) ($b[1]['q'] ?? '1') <=> (float) ($a[1]['q'] ?? '1')
        ));

        foreach ($values as $index => [$identifier, $parameters]) {
            if (preg_match('|^([^/]+)/([^/]+)$|', $identifier, $matches)) {
                yield $index => new ClientMediaType($matches[1], $matches[2], $parameters);
            }
        }
    }

    /**
     * @return Generator<int<0, max>, ClientLanguage>
     */
    public function getClientConsumedLanguages(): Generator
    {
        $header = $this->request->getHeaderLine('Accept-Language');
        if ($header === '') {
            return;
        }

        $values = HeaderParser::parseHeader($header);
        if ($values === []) {
            return;
        }

        // https://github.com/php/php-src/blob/d9549d2ee215cb04aa5d2e3195c608d581fb272c/ext/standard/array.c#L900-L903
        usort($values, static fn(array $a, array $b): int => (
            (float) ($b[1]['q'] ?? '1') <=> (float) ($a[1]['q'] ?? '1')
        ));

        foreach ($values as $index => [$identifier, $parameters]) {
            if (preg_match('|^(?:i-)?([^-]+)(?:-[^-]+)*$|', $identifier, $matches)) {
                yield $index => new ClientLanguage($matches[1], $identifier, $parameters);
            }
        }
    }

    public function getClientPreferredMediaType(MediaTypeInterface ...$serverProducedMediaTypes): ?ClientMediaType
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

    public function getClientPreferredLanguage(LanguageInterface ...$serverProducedLanguages): ?ClientLanguage
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
        // The server is ready to accept any media type.
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
        $clone->request = $this->request->withProtocolVersion($version);

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
        $clone->request = $this->request->withHeader($name, $value);

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function withAddedHeader($name, $value): self
    {
        $clone = clone $this;
        $clone->request = $this->request->withAddedHeader($name, $value);

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function withoutHeader($name): self
    {
        $clone = clone $this;
        $clone->request = $this->request->withoutHeader($name);

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
        $clone->request = $this->request->withBody($body);

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
        $clone->request = $this->request->withMethod($method);

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
        $clone->request = $this->request->withUri($uri, $preserveHost);

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
        $clone->request = $this->request->withRequestTarget($requestTarget);

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

    public function hasQueryParam(string $name): bool
    {
        $params = $this->request->getQueryParams();

        return isset($params[$name]);
    }

    public function getQueryParam(string $name, mixed $default = null): mixed
    {
        $params = $this->request->getQueryParams();

        return $params[$name] ?? $default;
    }

    /**
     * @inheritDoc
     */
    public function withQueryParams(array $query): self
    {
        $clone = clone $this;
        $clone->request = $this->request->withQueryParams($query);

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getCookieParams(): array
    {
        return $this->request->getCookieParams();
    }

    public function hasCookieParam(string $name): bool
    {
        $params = $this->request->getCookieParams();

        return isset($params[$name]);
    }

    public function getCookieParam(string $name, mixed $default = null): mixed
    {
        $params = $this->request->getCookieParams();

        return $params[$name] ?? $default;
    }

    /**
     * @inheritDoc
     */
    public function withCookieParams(array $cookies): self
    {
        $clone = clone $this;
        $clone->request = $this->request->withCookieParams($cookies);

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
        $clone->request = $this->request->withUploadedFiles($uploadedFiles);

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
        $clone->request = $this->request->withParsedBody($data);

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
        $clone->request = $this->request->withAttribute($name, $value);

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function withoutAttribute($name): self
    {
        $clone = clone $this;
        $clone->request = $this->request->withoutAttribute($name);

        return $clone;
    }
}
