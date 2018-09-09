<?php declare(strict_types=1);

namespace Fortuneglobe\Prometheus\Exporters\Tests\Unit\Application\Metrics;

use OpenMetricsPhp\Exposition\Text\Exceptions\InvalidArgumentException;
use OpenMetricsPhp\Exposition\Text\Label;
use OpenMetricsPhp\Exposition\Text\Tests\Traits\EmptyStringProviding;
use PHPUnit\Framework\TestCase;

final class LabelTest extends TestCase
{
	use EmptyStringProviding;

	/**
	 * @param string $name
	 *
	 * @throws \PHPUnit\Framework\AssertionFailedError
	 * @throws InvalidArgumentException
	 *
	 * @dataProvider emptyStringProvider
	 */
	public function testThrowsExceptionForEmptyName( string $name ) : void
	{
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Label name cannot be empty.' );

		Label::fromNameAndValue( $name, 'value' );

		$this->fail( 'Expected an InvalidArgumentException to be thrown for an empty label name.' );
	}

	/**
	 * @param string $value
	 *
	 * @throws InvalidArgumentException
	 * @throws \PHPUnit\Framework\AssertionFailedError
	 *
	 * @dataProvider emptyStringProvider
	 */
	public function testThrowsExceptionForEmptyValue( string $value ) : void
	{
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Label value cannot be empty.' );

		Label::fromNameAndValue( 'name', $value );

		$this->fail( 'Expected an InvalidArgumentException to be thrown for an empty label value.' );
	}

	/**
	 * @param string $name
	 *
	 * @throws InvalidArgumentException
	 * @throws \PHPUnit\Framework\AssertionFailedError
	 *
	 * @dataProvider invalidLabelNameWithWhitespaceProvider
	 */
	public function testThrowsExceptionForNameWithWhitespaces( string $name ) : void
	{
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Label names cannot contain whitespaces' );

		Label::fromNameAndValue( $name, 'value' );

		$this->fail( 'Expected an InvalidArgumentException to be thrown for a label name with whitespaces.' );
	}

	public function invalidLabelNameWithWhitespaceProvider() : array
	{
		return [
			[
				'name' => 'label with whitespace',
			],
			[
				'name' => "label-with\ttab",
			],
			[
				'name' => "label-with\nlinebreak",
			],
		];
	}

	/**
	 * @param string $name
	 * @param string $value
	 * @param string $expectedLabelString
	 *
	 * @throws InvalidArgumentException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 *
	 * @dataProvider labelStringsProvider
	 */
	public function testCanGetLabelString( string $name, string $value, string $expectedLabelString ) : void
	{
		$label = Label::fromNameAndValue( $name, $value );

		$this->assertSame( trim( $name ), $label->getName() );
		$this->assertSame( trim( $value ), $label->getValue() );
		$this->assertSame( $expectedLabelString, $label->asLabelString() );
	}

	public function labelStringsProvider() : array
	{
		return [
			[
				'name'                => 'unit',
				'value'               => 'test',
				'expectedLabelString' => 'unit="test"',
			],
			[
				'name'                => 'name',
				'value'               => 'value with whitespaces',
				'expectedLabelString' => 'name="value with whitespaces"',
			],
			[
				'name'                => ' name-with-surrounding-whitespaces ',
				'value'               => ' value with surrounding whitespaces ',
				'expectedLabelString' => 'name-with-surrounding-whitespaces="value with surrounding whitespaces"',
			],
			[
				'name'                => 'name',
				'value'               => 'value with "',
				'expectedLabelString' => 'name="value with \""',
			],
			[
				'name'                => 'name',
				'value'               => 'value with \\',
				'expectedLabelString' => 'name="value with \\\"',
			],
			[
				'name'                => 'name',
				'value'               => "value with\nlinebreak",
				'expectedLabelString' => 'name="value with\nlinebreak"',
			],
			[
				'name'                => 'name_with_underscore',
				'value'               => 'value',
				'expectedLabelString' => 'name_with_underscore="value"',
			],
			[
				'name'                => 'name_with_0123',
				'value'               => 'value',
				'expectedLabelString' => 'name_with_0123="value"',
			],
		];
	}

	/**
	 * @param string $labelString
	 * @param string $expectedName
	 * @param string $expectedValue
	 *
	 * @throws InvalidArgumentException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 *
	 * @dataProvider labelStringToNameValueProvider
	 */
	public function testCanGetNameAndValueFromLabelString(
		string $labelString,
		string $expectedName,
		string $expectedValue
	) : void
	{
		$label = Label::fromLabelString( $labelString );

		$this->assertSame( $expectedName, $label->getName() );
		$this->assertSame( $expectedValue, $label->getValue() );
	}

	public function labelStringToNameValueProvider() : array
	{
		return [
			[
				'labelString'   => 'unit="test"',
				'expectedName'  => 'unit',
				'expectedValue' => 'test',
			],
			[
				'labelString'   => 'name="value with whitespaces"',
				'expectedName'  => 'name',
				'expectedValue' => 'value with whitespaces',
			],
			[
				'labelString'   => 'name="value with \""',
				'expectedName'  => 'name',
				'expectedValue' => 'value with "',
			],
			[
				'labelString'   => 'name="value with \\\"',
				'expectedName'  => 'name',
				'expectedValue' => 'value with \\',
			],
			[
				'labelString'   => 'name="value with\nlinebreak"',
				'expectedName'  => 'name',
				'expectedValue' => "value with\nlinebreak",
			],
		];
	}

	/**
	 * @param string $labelString
	 *
	 * @throws InvalidArgumentException
	 * @throws \PHPUnit\Framework\AssertionFailedError
	 *
	 * @dataProvider invalidLabelStringProvider
	 */
	public function testThrowsExceptionForInvalidLabelStrings( string $labelString ) : void
	{
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Invalid label string.' );

		Label::fromLabelString( $labelString );

		$this->fail( 'Expected exception for invalid label string.' );
	}

	public function invalidLabelStringProvider() : array
	{
		return [
			[
				'labelString' => 'name=value',
			],
			[
				'labelString' => 'name and="value"',
			],
		];
	}
}