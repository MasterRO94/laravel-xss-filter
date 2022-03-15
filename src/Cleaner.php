<?php

declare(strict_types=1);

namespace MasterRO\LaravelXSSFilter;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Cleaner
{
    protected CleanerConfig $config;

    public function __construct(CleanerConfig $config)
    {
        $this->config = $config;
    }

    public function withConfig(CleanerConfig $config): Cleaner
    {
        $this->config = $config;

        return $this;
    }

    public function config(): CleanerConfig
    {
        return $this->config;
    }

    public function clean(string $value): string
    {
        $value = $this->escapeElements($value);
        $value = $this->cleanMediaElements($value);

        return $this->config->shouldEscapeInlineListeners()
            ? $this->escapeInlineEventListeners($value)
            : $this->removeInlineEventListeners($value);
    }

    public function escapeElements(string $value): string
    {
        preg_match_all($this->config->elementsPattern(), $value, $matches);

        foreach (Arr::get($matches, '0', []) as $htmlElement) {
            $value = str_replace($htmlElement, e($htmlElement), $value);
        }

        return $value;
    }

    public function cleanMediaElements(string $value): string
    {
        if (! $this->config->allowedMediaHosts()) {
            return $value;
        }

        $allowedUrls = collect($this->config->allowedMediaHosts())
            ->map(
                fn(string $host) => ! Str::startsWith($host, ['http', 'https', '//'])
                    ? ["http://{$host}", "https://{$host}", "//{$host}"]
                    : [$host]
            )
            ->flatten()
            ->all();

        preg_match_all($this->config->mediaElementsPattern(), $value, $matches);

        foreach (Arr::get($matches, '0', []) as $htmlElement) {
            preg_match_all('/src="(.*)"/isU', $htmlElement, $sources);

            $urls = Arr::get($sources, '1', []);

            foreach ($urls as $url) {
                if (! Str::startsWith($url, $allowedUrls)) {
                    $value = str_replace($url, '#!', $value);
                }
            }
        }

        return $value;
    }

    public function removeInlineEventListeners(string $value): string
    {
        foreach ($this->config->inlineListenersPatterns() as $pattern) {
            $value = preg_replace($pattern, '', $value);
        }

        return ! is_string($value) ? '' : $value;
    }

    public function escapeInlineEventListeners(string $value): string
    {
        foreach ($this->config->inlineListenersPatterns() as $pattern) {
            $value = preg_replace_callback($pattern, [$this, 'escapeEqualSign'], $value);
        }

        return ! is_string($value) ? '' : $value;
    }

    protected function escapeEqualSign(array $matches): string
    {
        return str_replace('=', '&#x3d;', $matches[0]);
    }
}
