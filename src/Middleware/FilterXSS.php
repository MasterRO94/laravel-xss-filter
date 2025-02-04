<?php

declare(strict_types=1);

namespace MasterRO\LaravelXSSFilter\Middleware;

use Illuminate\Foundation\Http\Middleware\TransformsRequest;
use MasterRO\LaravelXSSFilter\Cleaner\Cleaner;

class FilterXSS extends TransformsRequest
{
    /**
     * The attributes that should not be filtered.
     *
     * @var array
     */
    protected array $except = [];

    public function __construct(
        protected Cleaner $cleaner,
    ) {
        $this->except = config('xss-filter.except', []);
    }

    /**
     * Transform the given value.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return string|mixed
     */
    protected function transform($key, $value): mixed
    {
        if (in_array($key, $this->except, true)) {
            return $value;
        }

        if (!is_string($value)) {
            return $value;
        }

        return $this->cleaner->clean($value);
    }
}
