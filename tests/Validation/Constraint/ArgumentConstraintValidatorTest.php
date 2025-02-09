<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Validation\Constraint;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionParameter;
use Sunrise\Http\Router\Annotation\Constraint;
use Sunrise\Http\Router\Validation\Constraint\ArgumentConstraint;
use Sunrise\Http\Router\Validation\Constraint\ArgumentConstraintValidator;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validator\ContextualValidatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ArgumentConstraintValidatorTest extends TestCase
{
    private ArgumentConstraintValidator $constraintValidator;
    private ContextualValidatorInterface&MockObject $mockedContextualValidator;

    protected function setUp(): void
    {
        $validator = $this->createMock(ValidatorInterface::class);
        $translator = $this->createMock(TranslatorInterface::class);
        $executionContext = new ExecutionContext($validator, null, $translator);

        $this->constraintValidator = new ArgumentConstraintValidator();
        $this->constraintValidator->initialize($executionContext);

        $this->mockedContextualValidator = $this->createMock(ContextualValidatorInterface::class);
        $validator->method('inContext')->with($executionContext)->willReturn($this->mockedContextualValidator);
    }

    public function testValidate(): void
    {
        $parameter = new ReflectionParameter(static fn(#[Constraint(new NotBlank(), new Length(max: 255))] string $p) => null, 'p');
        $constraint = new ArgumentConstraint($parameter);

        $this->mockedContextualValidator->expects(self::once())->method('validate')->willReturnCallback(
            function (mixed $value, array $constraints) {
                $this->assertSame('foo', $value);
                $this->assertCount(2, $constraints);
                return $this->mockedContextualValidator;
            }
        );

        $this->constraintValidator->validate('foo', $constraint);
    }

    public function testUnsupportedParameter(): void
    {
        $parameter = new ReflectionParameter(static fn($p) => null, 'p');
        $constraint = new ArgumentConstraint($parameter);
        $this->mockedContextualValidator->expects(self::never())->method('validate');
        $this->constraintValidator->validate(null, $constraint);
    }

    public function testParameterWithoutConstraints(): void
    {
        $parameter = new ReflectionParameter(static fn(#[Constraint] $p) => null, 'p');
        $constraint = new ArgumentConstraint($parameter);
        $this->mockedContextualValidator->expects(self::never())->method('validate');
        $this->constraintValidator->validate(null, $constraint);
    }

    public function testUnexpectedConstraint(): void
    {
        $constraint = $this->createMock(\Symfony\Component\Validator\Constraint::class);
        $this->expectException(UnexpectedTypeException::class);
        $this->constraintValidator->validate(null, $constraint);
    }
}
