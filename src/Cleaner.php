<?php

declare(strict_types=1);

namespace MasterRO\LaravelXSSFilter;

class Cleaner
{
	/**
	 * @var string
	 */
	protected $scriptsAndIframesPattern = '/(<script.*script>|<frame.*frame>|<iframe.*iframe>|<object.*object>|<embed.*embed>)/isU';

	/**
	 * @var string
	 */
	protected $inlineListenersPattern = '/on.*=\".*\"(?=.*>)/isU';


	/**
	 * @param string $value
	 *
	 * @return string
	 */
	public function clean(string $value): string
	{
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
