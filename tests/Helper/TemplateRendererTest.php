<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Helper;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Sunrise\Http\Router\Helper\TemplateRenderer;

use function file_put_contents;
use function sys_get_temp_dir;
use function unlink;

final class TemplateRendererTest extends TestCase
{
    public function testFailsWhenFileThrowsError(): void
    {
        $templateFilename = sys_get_temp_dir() . '/6b07a200-3ccc-4e59-9de4-b404b59cf554';
        file_put_contents($templateFilename, '<?php throw new RuntimeException("Whoops!");');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Whoops!');

        try {
            TemplateRenderer::renderTemplate($templateFilename, variables: []);
        } finally {
            unlink($templateFilename);
        }
    }
}
