<?php

declare(strict_types=1);

namespace MasterRO\LaravelXSSFilter;

use Illuminate\Foundation\Http\Middleware\TransformsRequest;

/**
 * Class FilterXSS
 *
 * @package MasterRO\LaravelXSSFilter
 */
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
     * @param string $key
     * @param mixed $value
     *
     * @return string|mixed
     */
    protected function transform($key, $value)
    {
        if (in_array($key, $this->except, true)) {
            return $value;
        }

        if (! is_string($value)) {
            return $value;
        }

        return $this->cleaner->clean($value);
    }

}
