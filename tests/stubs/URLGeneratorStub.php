<?php

declare(strict_types=1);
// SPDX-FileCopyrightText: 2023 Alec Kojaev <alec@kojaev.name>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Stubs;

use OCP\IURLGenerator;

trait URLGeneratorStub {
	protected IURLGenerator $urlGenerator;

	protected function initUrlGenerator(): void {
		$generator = $this->createStub(IURLGenerator::class);
		$generator->method('linkToRoute')->willReturnCallback(function (string $routeName, array $arguments = []): string {
			return 'route:' . $routeName . ':' . implode(':', array_map(fn ($k, $v) => $k . '=' . $v, array_keys($arguments), array_values($arguments)));
		});
		$generator->method('imagePath')->willReturnCallback(function (string $appName, string $file): string {
			return 'image-path:' . $appName . ':' . $file;
		});
		$this->urlGenerator = $generator;
	}
}
