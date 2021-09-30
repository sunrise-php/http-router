<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Test\Fixture\Controllers\Annotated;

use Sunrise\Http\Router\Test\Fixture\Controllers\AbstractController;

final class GroupedAnnotatedController extends AbstractController
{

    /**
     * @Route(
     *   name="first-from-grouped-annotated-controller",
     *   path="/",
     *   method="GET",
     * )
     */
    public function first($request)
    {
        return $this->handle($request);
    }

    /**
     * @Route(
     *   name="second-from-grouped-annotated-controller",
     *   path="/",
     *   method="GET",
     * )
     */
    public function second($request)
    {
        return $this->handle($request);
    }

    /**
     * @Route(
     *   name="private-from-grouped-annotated-controller",
     *   path="/",
     *   method="GET",
     * )
     */
    private function privateAction()
    {
    }

    /**
     * @Route(
     *   name="protected-from-grouped-annotated-controller",
     *   path="/",
     *   method="GET",
     * )
     */
    protected function protectedAction()
    {
    }

    /**
     * @Route(
     *   name="static-from-grouped-annotated-controller",
     *   path="/",
     *   method="GET",
     * )
     */
    public static function staticAction()
    {
    }

    public function shouldBeIgnored()
    {
    }
}
