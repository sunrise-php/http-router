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

use Sunrise\Http\Router\Exception\CodecException;

use function in_array;
use function sprintf;

/**
 * @since 3.0.0
 */
final class CodecManager implements CodecManagerInterface
{
    public function __construct(
        /** @var array<array-key, CodecInterface> */
        private readonly array $codecs,
        /** @var array<array-key, mixed> */
        private readonly array $context = [],
    ) {
    }

    public function supportsMediaType(MediaTypeInterface ...$mediaTypes): bool
    {
        foreach ($this->codecs as $codec) {
            foreach ($mediaTypes as $mediaType) {
                if (in_array($mediaType, $codec->getSupportedMediaTypes(), true)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function encode(MediaTypeInterface $mediaType, mixed $data, array $context): string
    {
        return $this->getCodec($mediaType)->encode($data, $context + $this->context);
    }

    /**
     * @inheritDoc
     */
    public function decode(MediaTypeInterface $mediaType, string $data, array $context): mixed
    {
        return $this->getCodec($mediaType)->decode($data, $context + $this->context);
    }

    /**
     * @throws CodecException
     */
    private function getCodec(MediaTypeInterface $mediaType): CodecInterface
    {
        foreach ($this->codecs as $codec) {
            if (in_array($mediaType, $codec->getSupportedMediaTypes(), true)) {
                return $codec;
            }
        }

        throw new CodecException(sprintf(
            'Unsupported the "%s" media type.',
            $mediaType->getIdentifier(),
        ));
    }
}
