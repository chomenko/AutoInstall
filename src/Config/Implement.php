<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 * Created: 29.11.2018 10:20
 */

namespace Chomenko\AutoInstall\Config;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("CLASS")
 */
class Implement
{

	/**
	 * @var string
	 * @internal
	 */
	private $factory;

	/**
	 * @param array $factory
	 * @throws \Exception
	 */
	public function __construct(array $factory)
	{
		$value = $factory["value"];
		if (!is_string($value)) {
			throw new \Exception("Annotation  @Factory required string @Factory(\"Factory\\Class\")");
		}
		$this->factory = $value;
	}

	/**
	 * @return string
	 */
	public function getValue(): string
	{
		return $this->factory;
	}

}
