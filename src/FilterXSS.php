<?php

declare(strict_types=1);

namespace MasterRO\LaravelXSSFilter;

use Illuminate\Foundation\Http\Middleware\TransformsRequest;

class FilterXSS extends TransformsRequest
{
	/**
	 * The attributes that should not be trimmed.
	 *
	 * @var array
	 */
	protected $except = [
		'password',
		'password_confirmation',
	];

	/**
	 * @var string
	 */
	protected $scriptsAndIframesPattern = '/(<script.*script>|<frame.*frame>|<iframe.*iframe>|<object.*object>|<embed.*embed>)/isU';

	/**
	 * @var string
	 */
	protected $inlineListenersPattern = '/on.*=\".*\"(?=.*>)/isU';


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

		$value = $this->escapeScriptsAndIframes($value);
		$value = $this->removeInlineEventListeners($value);

		return $value;
	}


	/**
	 * @param $value
	 *
	 * @return mixed
	 */
	protected function escapeScriptsAndIframes(string $value): string
	{
		preg_match_all($this->scriptsAndIframesPattern, $value, $matches);

		foreach (array_get($matches, '0', []) as $script) {
			$value = str_replace($script, e($script), $value);
		}

		return $value;
	}


	/**
	 * @param string $value
	 *
	 * @return null|string
	 */
	protected function removeInlineEventListeners(string $value): string
	{
		return preg_replace($this->inlineListenersPattern, '', $value);
	}

}
