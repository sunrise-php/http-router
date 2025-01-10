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
use Sunrise\Http\Router\Dictionary\HeaderName;
use Sunrise\Http\Router\Entity\Locale\Locale;
use Sunrise\Http\Router\Entity\Locale\LocaleComparator;
use Sunrise\Http\Router\Entity\Locale\LocaleInterface;
use Sunrise\Http\Router\Entity\MediaType\MediaType;
use Sunrise\Http\Router\Entity\MediaType\MediaTypeComparator;
use Sunrise\Http\Router\Entity\MediaType\MediaTypeInterface;
use Sunrise\Http\Router\Helper\HeaderParser;

use function extension_loaded;
use function preg_match;
use function reset;
use function usort;

/**
 * @since 3.0.0
 */
final class ServerRequest implements ServerRequestInterface
{
    public function __construct(
        private ServerRequestInterface $request,
    ) {
    }

    public static function create(ServerRequestInterface $request): self
    {
        return ($request instanceof self) ? $request : new self($request);
    }

    /**
     * @throws LogicException If the request doesn't contain a route.
     */
    public function getRoute(): RouteInterface
    {
        $route = $this->request->getAttribute(RouteInterface::class);

        if (! $route instanceof RouteInterface) {
            throw new LogicException(
                'At this level of the application, the request does not contain information about the requested route.'
            );
        }

        return $route;
    }

    public function getClientProducedMediaType(): ?MediaTypeInterface
    {
        $values = HeaderParser::parseHeader($this->request->getHeaderLine(HeaderName::CONTENT_TYPE));
        if ($values === []) {
            return null;
        }

        [$identifier] = reset($values);
        if (preg_match('|^([^/*]+)/([^/*]+)$|', $identifier) === 1) {
            return new MediaType($identifier);
        }

        return null;
    }

    /**
     * @return Generator<int, LocaleInterface>
     *
     * @throws LogicException
     */
    public function getClientConsumedLocales(): Generator
    {
        if (!extension_loaded('intl')) {
            throw new LogicException(
                'To get the locales consumed by the client, ' .
                'the Intl (https://www.php.net/intl) extension must be installed.'
            );
        }

        $values = HeaderParser::parseHeader($this->request->getHeaderLine(HeaderName::ACCEPT_LANGUAGE));
        if ($values === []) {
            return;
        }

        // https://github.com/php/php-src/blob/d9549d2ee215cb04aa5d2e3195c608d581fb272c/ext/standard/array.c#L900-L903
        usort($values, static fn(array $a, array $b): int => (
            (float) ($b[1]['q'] ?? '1') <=> (float) ($a[1]['q'] ?? '1')
        ));

        foreach ($values as [$identifier]) {
            $subtags = \Locale::parseLocale($identifier);
            if (isset($subtags['language'])) {
                /** @var string $languageCode */
                $languageCode = $subtags['language'];
                /** @var string|null $regionCode */
                $regionCode = $subtags['region'] ?? null;

                yield new Locale($languageCode, $regionCode);
            }
        }
    }

    /**
     * @return Generator<int<0, max>, MediaTypeInterface>
     */
    public function getClientConsumedMediaTypes(): Generator
    {
        $values = HeaderParser::parseHeader($this->request->getHeaderLine(HeaderName::ACCEPT));
        if ($values === []) {
            return;
        }

        // https://github.com/php/php-src/blob/d9549d2ee215cb04aa5d2e3195c608d581fb272c/ext/standard/array.c#L900-L903
        usort($values, static fn(array $a, array $b): int => (
            (float) ($b[1]['q'] ?? '1') <=> (float) ($a[1]['q'] ?? '1')
        ));

        foreach ($values as [$identifier]) {
            if (preg_match('|^([^/]+)/([^/]+)$|', $identifier) === 1) {
                yield new MediaType($identifier);
            }
        }
    }

    /**
     * @param T ...$serverProducedLocales
     *
     * @return T|null
     *
     * @template T of LocaleInterface
     */
    public function getClientPreferredLocale(LocaleInterface ...$serverProducedLocales): ?LocaleInterface
    {
        if ($serverProducedLocales === []) {
            return null;
        }

        foreach ($this->getClientConsumedLocales() as $clientConsumedLanguage) {
            foreach ($serverProducedLocales as $serverProducedLanguage) {
                if (LocaleComparator::compare($clientConsumedLanguage, $serverProducedLanguage) === 0) {
                    return $serverProducedLanguage;
                }
            }
        }

        return null;
    }

    /**
     * @param T ...$serverProducedMediaTypes
     *
     * @return T|null
     *
     * @template T of MediaTypeInterface
     */
    public function getClientPreferredMediaType(MediaTypeInterface ...$serverProducedMediaTypes): ?MediaTypeInterface
    {
        if ($serverProducedMediaTypes === []) {
            return null;
        }

        foreach ($this->getClientConsumedMediaTypes() as $clientConsumedMediaType) {
            foreach ($serverProducedMediaTypes as $serverProducedMediaType) {
                if (MediaTypeComparator::compare($clientConsumedMediaType, $serverProducedMediaType) === 0) {
                    return $serverProducedMediaType;
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

        foreach ($serverConsumedMediaTypes as $serverConsumedMediaType) {
            if (MediaTypeComparator::compare($clientProducedMediaType, $serverConsumedMediaType) === 0) {
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
     * {@inheritDoc}
     *
     * @return array<array-key, mixed>
     */
    public function getServerParams(): array
    {
        return $this->request->getServerParams();
    }

    /**
     * {@inheritDoc}
     *
     * @return array<array-key, mixed>
     */
    public function getQueryParams(): array
    {
        return $this->request->getQueryParams();
    }

    public function hasQueryParam(string $name): bool
    {
        return isset($this->request->getQueryParams()[$name]);
    }

    public function getQueryParam(string $name, mixed $default = null): mixed
    {
        return $this->request->getQueryParams()[$name] ?? $default;
    }

    /**
     * {@inheritDoc}
     *
     * @param array<array-key, mixed> $query
     *
     * @return static
     */
    public function withQueryParams(array $query): self
    {
        $clone = clone $this;
        $clone->request = $this->request->withQueryParams($query);

        return $clone;
    }

    /**
     * {@inheritDoc}
     *
     * @return array<array-key, mixed>
     */
    public function getCookieParams(): array
    {
        return $this->request->getCookieParams();
    }

    public function hasCookieParam(string $name): bool
    {
        return isset($this->request->getCookieParams()[$name]);
    }

    public function getCookieParam(string $name, mixed $default = null): mixed
    {
        return $this->request->getCookieParams()[$name] ?? $default;
    }

    /**
     * {@inheritDoc}
     *
     * @param array<array-key, mixed> $cookies
     *
     * @return static
     */
    public function withCookieParams(array $cookies): self
    {
        $clone = clone $this;
        $clone->request = $this->request->withCookieParams($cookies);

        return $clone;
    }

    /**
     * {@inheritDoc}
     *
     * @return array<array-key, mixed>
     */
    public function getUploadedFiles(): array
    {
        return $this->request->getUploadedFiles();
    }

    /**
     * {@inheritDoc}
     *
     * @param array<array-key, mixed> $uploadedFiles
     *
     * @return static
     */
    public function withUploadedFiles(array $uploadedFiles): self
    {
        $clone = clone $this;
        $clone->request = $this->request->withUploadedFiles($uploadedFiles);

        return $clone;
    }

    /**
     * {@inheritDoc}
     *
     * @return array<array-key, mixed>|object|null
     */
    public function getParsedBody(): mixed
    {
        return $this->request->getParsedBody();
    }

    /**
     * {@inheritDoc}
     *
     * @param array<array-key, mixed>|object|null $data
     *
     * @return static
     */
    public function withParsedBody($data): self
    {
        $clone = clone $this;
        $clone->request = $this->request->withParsedBody($data);

        return $clone;
    }

    /**
     * {@inheritDoc}
     *
     * @return array<array-key, mixed>
     */
    public function getAttributes(): array
    {
        return $this->request->getAttributes();
    }

    /**
     * {@inheritDoc}
     *
     * @param string $name
     * @param mixed $default
     *
     * @return mixed
     */
    public function getAttribute($name, $default = null): mixed
    {
        return $this->request->getAttribute($name, $default);
    }

    /**
     * {@inheritDoc}
     *
     * @param string $name
     * @param mixed $value
     *
     * @return static
     */
    public function withAttribute($name, $value): self
    {
        $clone = clone $this;
        $clone->request = $this->request->withAttribute($name, $value);

        return $clone;
    }

    /**
     * {@inheritDoc}
     *
     * @param string $name
     *
     * @return static
     */
    public function withoutAttribute($name): self
    {
        $clone = clone $this;
        $clone->request = $this->request->withoutAttribute($name);

        return $clone;
    }
}
