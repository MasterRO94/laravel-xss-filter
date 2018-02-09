<?php

declare(strict_types=1);

namespace MasterRO\LaravelXSSFilter;

use Illuminate\Foundation\Http\Middleware\TransformsRequest;

class FilterXSS extends TransformsRequest
{
	/**
	 * The attributes that should not be filtered.
	 *
	 * @var array
	 */
	protected $except = [];

	/**
	 * @var Cleaner
	 */
	protected $cleaner;


	/**
	 * FilterXSS constructor.
	 *
	 * @param Cleaner $cleaner
	 */
	public function __construct(Cleaner $cleaner)
	{
		$this->except = config('xss-filter.except', []);
		$this->cleaner = $cleaner;
	}


	/**
	 * Transform the given value.
	 *
	 * @param  string $key
	 * @param  mixed $value
	 *
	 * @return mixed
	 */
	protected function transform($key, $value)
	{
		if (in_array($key, $this->except, true)) {
			return $value;
		}

		if (! is_string($value)) {
			return $value;
		}

		$value = $this->cleaner->clean($value);

		return $value;
	}

}
