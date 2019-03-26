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
	 * Clean
	 *
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
	 * Escape Scripts And Iframes
	 *
	 * @param string $value
	 *
	 * @return string
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
	 * Remove Inline Event Listeners
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	protected function removeInlineEventListeners(string $value): string
	{
		$string = preg_replace($this->inlineListenersPattern, '', $value);

		return !is_string($string) ? '' : $string;
	}
}
