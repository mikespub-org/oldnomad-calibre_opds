<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: 2023 Alec Kojaev <alec@kojaev.name>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\Calibre2OPDS\Opds;

use DateTimeInterface;

/**
 * Container for OPDS/Atom feed entry.
 */
class OpdsEntry {
	/**
	 * Entry ID.
	 */
	private string $id;
	/**
	 * Entry title.
	 */
	private string $title;
	/**
	 * Optional entry summary (contents).
	 */
	private ?string $summary;
	/**
	 * Optional entry "last updated" timestamp.
	 */
	private ?DateTimeInterface $updated;
	/**
	 * List of entry authors.
	 *
	 * @var array<OpdsAuthor>
	 */
	private array $authors;
	/**
	 * List of entry categories.
	 *
	 * @var array<OpdsCategory>
	 */
	private array $categories;
	/**
	 * List of entry links.
	 *
	 * @var array<OpdsLink>
	 */
	private array $links;
	/**
	 * List of entry simple attributes.
	 *
	 * @var array<OpdsAttribute>
	 */
	private array $attributes;

	/**
	 * Construct an instance.
	 *
	 * @param string $id entry ID.
	 * @param string $title entry title.
	 * @param string|null $summary entry summary (contents), or `null` if not defined.
	 */
	public function __construct(string $id, string $title, ?string $summary = null) {
		$this->id = $id;
		$this->title = $title;
		$this->summary = $summary;
		$this->updated = null;
		$this->authors = [];
		$this->categories = [];
		$this->links = [];
		$this->attributes = [];
	}

	/**
	 * Get entry ID.
	 *
	 * @return string entry ID.
	 */
	public function getId(): string {
		return $this->id;
	}

	/**
	 * Get entry title.
	 *
	 * @return string entry title.
	 */
	public function getTitle(): string {
		return $this->title;
	}

	/**
	 * Get entry summary (contents).
	 *
	 * @return string|null entry summary (contents), or `null` if not defined.
	 */
	public function getSummary(): ?string {
		return $this->summary;
	}

	/**
	 * Set entry "last updated" timestamp.
	 *
	 * @param DateTimeInterface|null $updated entry updated timestamp.
	 * @return self
	 */
	public function setUpdated(?DateTimeInterface $updated): self {
		$this->updated = $updated;
		return $this;
	}

	/**
	 * Get entry "last updated" timestamp.
	 *
	 * @return DateTimeInterface|null entry updated timestamp.
	 */
	public function getUpdated(): ?DateTimeInterface {
		return $this->updated;
	}

	/**
	 * Add an author for the entry.
	 *
	 * @param OpdsAuthor $author entry author.
	 * @return self
	 */
	public function addAuthor(OpdsAuthor $author): self {
		$this->authors[] = $author;
		return $this;
	}

	/**
	 * Get entry authors.
	 *
	 * @return array<OpdsAuthor> entry authors.
	 */
	public function getAuthors(): array {
		return $this->authors;
	}

	/**
	 * Add a category for the entry.
	 *
	 * @param OpdsCategory $cat entry category.
	 * @return self
	 */
	public function addCategory(OpdsCategory $cat): self {
		$this->categories[] = $cat;
		return $this;
	}

	/**
	 * Get entry categories.
	 *
	 * @return array<OpdsCategory> entry categories.
	 */
	public function getCategories(): array {
		return $this->categories;
	}

	/**
	 * Add a link for the entry.
	 *
	 * @param OpdsLink $link entry link.
	 * @return self
	 */
	public function addLink(OpdsLink $link): self {
		$this->links[] = $link;
		return $this;
	}

	/**
	 * Get entry links.
	 *
	 * @return array<OpdsLink> entry links.
	 */
	public function getLinks(): array {
		return $this->links;
	}

	/**
	 * Add a simple attribute for the entry.
	 *
	 * @param OpdsAttribute $attr entry attribute.
	 * @return self
	 */
	public function addAttribute(OpdsAttribute $attr): self {
		$this->attributes[] = $attr;
		return $this;
	}

	/**
	 * Get entry attributes.
	 *
	 * @return array<OpdsAttribute> entry attributes.
	 */
	public function getAttributes(): array {
		return $this->attributes;
	}
}
