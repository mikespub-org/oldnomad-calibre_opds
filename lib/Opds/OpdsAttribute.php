<?php

declare(strict_types=1);

namespace OCA\Calibre2OPDS\Opds;

use DateTimeInterface;

/**
 * Container for Atom/OPDS feed entry simple attribute.
 *
 * This class implements container for a simple tag without additional attributes
 * and either without contents, or with a plain text contents.
 *
 * Tag contents can be passed either as a scalar (e.g. a string or an integer),
 * or as an object implementing `DateTimeInterface` for timestamps.
 */
class OpdsAttribute {
	/**
	 * Construct an instance.
	 *
	 * @param string|null $ns tag namespace prefix, or `null` for Atom tags.
	 * @param string $tag tag name.
	 * @param scalar|DateTimeInterface|null $value tag contents, or `null`.
	 */
	public function __construct(private ?string $ns, private string $tag, private mixed $value) {
	}

	/**
	 * Get tag namespace prefix.
	 *
	 * @return string|null tag namespace prefix, or `null` for Atom tags.
	 */
	public function getNs(): ?string {
		return $this->ns;
	}

	/**
	 * Get tag name.
	 *
	 * @return string tag name.
	 */
	public function getTag(): string {
		return $this->tag;
	}

	/**
	 * Get tag contents as a text.
	 *
	 * @return string|null tag contents as a text, or `null` if no contents.
	 */
	public function getValueText(): ?string {
		if (is_null($this->value)) {
			return null;
		}
		if ($this->value instanceof DateTimeInterface) {
			return $this->value->format('c');
		}
		return strval($this->value);
	}
}
