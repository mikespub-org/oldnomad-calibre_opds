<?php

declare(strict_types=1);

namespace Stubs;

use Exception;
use OCA\Calibre2OPDS\Calibre\CalibreItem;
use OCA\Calibre2OPDS\Calibre\ICalibreDB;
use ReflectionClass;

trait CalibreStub {
	protected function createCalibreItem(string $cls, ICalibreDB $db, array $data, ?array $subData = null): CalibreItem {
		$instCls = new ReflectionClass($cls);
		if (!$instCls->isSubclassOf(CalibreItem::class)) {
			throw new Exception('class '.$cls.' is not a subclass of CalibreItem');
		}
		$instConstructor = $instCls->getConstructor();
		$instConstructor->setAccessible(true);
		/** @var CalibreItem */
		$inst = $instCls->newInstanceWithoutConstructor();
		$instConstructor->invoke($inst, $db, $data);
		if (!is_null($subData)) {
			$prop = $instCls->getParentClass()->getProperty('data');
			$prop->setAccessible(true);
			$value = $prop->getValue($inst);
			$prop->setValue($inst, array_merge($value, $subData));
		}
		return $inst;
	}
}
