<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: 2023 Alec Kojaev <alec@kojaev.name>
// SPDX-License-Identifier: AGPL-3.0-or-later

use OCA\Calibre2OPDS\Util\CachedIterator;
use OCA\Calibre2OPDS\Util\MapAggregate;
use OCA\Calibre2OPDS\Util\MimeTypes;
use PHPUnit\Framework\TestCase;

final class UtilTest extends TestCase {
	public function testMimeTypes() {
		$this->assertFalse(MimeTypes::loadMimeTypes(__DIR__ . '/../files/nonexistent.types'));
		$this->assertTrue(MimeTypes::loadMimeTypes(__DIR__ . '/../files/broken.types'));
		$this->assertEquals('application/x-ms-reader', MimeTypes::getMimeType('LIT'));
		$this->assertEquals('application/octet-stream', MimeTypes::getMimeType('XXX'));
	}

	public function testCachedIterator() {
		$sample = new CachedIterator(new NoRewindIterator(SplFixedArray::fromArray([0, 1, 2, 3, 4, 5, 6, 7, 8, 9])->getIterator()));
		$this->assertTrue($sample->valid(), 'Cached iterator -- first element validity');
		$this->assertEquals(0, $sample->current(), 'Cached iterator -- first element');
		$sample->next();
		$this->assertTrue($sample->valid(), 'Cached iterator -- second element validity');
		$this->assertEquals(1, $sample->current(), 'Cached iterator -- second element');
		$sample->rewind();
		$test = iterator_to_array($sample);
		$this->assertEquals([0, 1, 2, 3, 4, 5, 6, 7, 8, 9], $test, 'Cached iterator -- after rewind');
		$sample->rewind();
		$test = iterator_to_array($sample);
		$this->assertEquals([0, 1, 2, 3, 4, 5, 6, 7, 8, 9], $test, 'Cached iterator -- after second rewind');
	}

	public function testMapIterator() {
		$sample = SplFixedArray::fromArray([0, 1, 2, 3, 4, 5, 6, 7, 8, 9]);
		$test = iterator_to_array(new MapAggregate(
			$sample, fn ($v) => $v * $v
		));
		$this->assertEquals([0, 1, 4, 9, 16, 25, 36, 49, 64, 81], $test, 'Non-filtering iterator');

		$test = iterator_to_array(new MapAggregate(
			$sample,
			fn ($v) => $v * $v,
			fn ($v): bool => ($v % 3) == 1
		));
		$this->assertEquals(
			[1 => 1, 2 => 4, 4 => 16, 5 => 25, 7 => 49, 8 => 64],
			$test, 'Filtering iterator'
		);

		$test = iterator_to_array(new MapAggregate(
			$sample,
			fn ($v) => $v * $v,
			fn ($v): bool => $v > 100
		));
		$this->assertEquals([], $test, 'Empty filtering iterator');
	}
}
