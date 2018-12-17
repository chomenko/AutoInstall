<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 * Created: 29.11.2018 10:20
 */

namespace Chomenko\AutoInstall;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("CLASS")
 */
class Tag
{

	/**
	 * @var array
	 * @internal
	 */
	private $tags = [];

	public function __construct(array $tags)
	{
		$this->tags = $tags;
	}

	/**
	 * @return array
	 */
	public function getTags(): array
	{
		$value = [];
		if (array_key_exists("value", $this->tags)) {
			if (!is_array($this->tags["value"])) {
				$value[] = $this->tags["value"];
				return $value;
			}

			return $this->tags["value"];
		}
		return $value;
	}
}
