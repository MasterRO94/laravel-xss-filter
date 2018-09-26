<?php

declare(strict_types=1);

namespace MasterRO\LaravelXSSFilter;

use Illuminate\Support\Facades\Facade;

class XSSCleanerFacade extends Facade
{
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 * @throws \RuntimeException
	 */
	protected static function getFacadeAccessor()
	{
		return Cleaner::class;
	}
}
