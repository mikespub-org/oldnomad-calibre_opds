<?php

declare(strict_types=1);

namespace OCA\Calibre2OPDS\Opds;

/**
 * Container for Atom entry category.
 */
class OpdsCategory {
	/**
	 * Construct an instance.
	 *
	 * @param string $term category term.
	 * @param string|null $schema category schema URI, or `null` if not defined.
	 * @param string|null $label category label, or `null` if not defined.
	 */
	public function __construct(private string $term, private ?string $schema = null, private ?string $label = null) {
	}

	/**
	 * Get category term.
	 *
	 * @return string category term.
	 */
	public function getTerm(): string {
		return $this->term;
	}

	/**
	 * Get category schema URI.
	 *
	 * @return string|null category schema URI, or `null` if not defined.
	 */
	public function getSchema(): ?string {
		return $this->schema;
	}

	/**
	 * Get category label.
	 *
	 * @return string|null category label, or `null` if not defined.
	 */
	public function getLabel(): ?string {
		return $this->label;
	}
}
