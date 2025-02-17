<?php

declare(strict_types=1);

namespace MasterRO\LaravelXSSFilter\Cleaner;

use Illuminate\Support\Str;

class CleanerConfig
{
    protected ?array $allowedElements = null;

    protected array $deniedElements = ['script', 'frame', 'iframe', 'object', 'embed'];

    protected array $mediaElements = ['img', 'audio', 'video', 'iframe'];

    // If this value set to `true` inline listeners will be escaped, otherwise they will be removed.
    protected bool $escapeInlineListeners = false;

    /**
     * Image/Audio/Video/Iframe hosts that should be retained (by default, all hosts are allowed).
     *
     * @var list<string>|null
     */
    protected ?array $allowedMediaHosts = null;

    protected string $inlineListenersPattern = '/\bon\w+\s*=\s*([\'"])(.*?)\1|javascript:[^"\' >]*/is';

    protected string $malformedListenersPattern = '/\bon\w+\s*=\s*([\'"])?([^\'"\s>]+)\1?(?=\s|>)/i';

    public static function make(): CleanerConfig
    {
        return new static();
    }

    public static function fromArray(array $params): CleanerConfig
    {
        $config = static::make();

        foreach ($params as $key => $value) {
            $setter = 'set' . Str::camel($key);

            if (method_exists($config, $setter)) {
                $config->{$setter}($value);
            }
        }

        return $config;
    }

    /**
     * Configures the given element as allowed.
     *
     * Allowed elements are elements the cleaner should retain from the input.
     */
    public function allowElement(string $element): CleanerConfig
    {
        $this->allowedElements[] = $element;

        return $this;
    }

    /**
     * Configures the given element as not allowed.
     *
     * Denied elements are elements the cleaner should escape from the input.
     */
    public function denyElement(string $element): CleanerConfig
    {
        $this->deniedElements[] = $element;

        return $this;
    }

    /**
     * Alias for ::denyElement()
     */
    public function blockElement(string $element): CleanerConfig
    {
        return $this->denyElement($element);
    }

    /**
     * Configures the given element as media.
     *
     * Allowed elements are elements the cleaner should retain from the input.
     */
    public function addMediaElement(string $element): CleanerConfig
    {
        $this->mediaElements[] = $element;

        return $this;
    }

    /**
     * Configures the given element as not media.
     *
     * Allowed elements are elements the cleaner should retain from the input.
     */
    public function removeMediaElement(string $element): CleanerConfig
    {
        $this->mediaElements = array_filter(
            $this->mediaElements,
            fn(string $el) => $el !== $element,
        );

        return $this;
    }

    /**
     * Allows only a given list of hosts to be used in media source attributes (img, audio, video, iframe...).
     *
     * All other hosts will be dropped. By default, all hosts are allowed
     * ($allowMediaHosts = null).
     *
     * @param list<string>|null $allowMediaHosts
     */
    public function allowMediaHosts(?array $allowMediaHosts): CleanerConfig
    {
        $this->allowedMediaHosts = $allowMediaHosts;

        return $this;
    }

    public function elementsPattern(): string
    {
        $pattern = collect($this->deniedElements)
            ->reject(fn(string $element) => $this->allowedElements && in_array($element, $this->allowedElements))
            ->map(fn(string $element) => "<{$element}.*{$element}>")
            ->implode('|');

        return "/({$pattern})/isU";
    }

    public function mediaElementsPattern(): string
    {
        $pattern = collect($this->mediaElements)
            ->map(fn(string $element) => "<{$element}.*{$element}>")
            ->implode('|');

        return "/({$pattern})/isU";
    }

    /**
     * @return list<string>|array|string[]
     */
    public function inlineListenersPatterns(): array
    {
        return [$this->inlineListenersPattern, $this->malformedListenersPattern];
    }

    public function shouldEscapeInlineListeners(): bool
    {
        return $this->escapeInlineListeners;
    }

    public function allowedMediaHosts(): ?array
    {
        return $this->allowedMediaHosts;
    }

    public function setAllowedElements(?array $allowedElements): CleanerConfig
    {
        $this->allowedElements = $allowedElements;

        return $this;
    }

    public function setDeniedElements(array $deniedElements): CleanerConfig
    {
        $this->deniedElements = $deniedElements;

        return $this;
    }

    public function setBlockedElements(array $blockedElements): CleanerConfig
    {
        return $this->setDeniedElements($blockedElements);
    }

    public function setMediaElements(array $mediaElements): CleanerConfig
    {
        $this->mediaElements = $mediaElements;

        return $this;
    }

    public function setEscapeInlineListeners(bool $escapeInlineListeners): CleanerConfig
    {
        $this->escapeInlineListeners = $escapeInlineListeners;

        return $this;
    }

    public function setAllowedMediaHosts(?array $allowedMediaHosts): CleanerConfig
    {
        $this->allowedMediaHosts = $allowedMediaHosts;

        return $this;
    }
}
