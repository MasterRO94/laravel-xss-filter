<?php

declare(strict_types=1);

namespace MasterRO\LaravelXSSFilter\Middleware;

use Closure;
use Illuminate\Http\Request;

class FilterXSSLivewire extends FilterXSS
{
    public function handle($request, Closure $next)
    {
        if (! $this->isLivewireRequest($request)) {
            return $next($request);
        }

        $this->cleanLivewirePayload($request);

        return $next($request);
    }

    protected function cleanLivewirePayload(Request $request): void
    {
        $components = $request->input('components');

        foreach ($components as $i => &$component) {
            if (isset($component['updates'])) {
                $component['updates'] = $this->cleanArray($component['updates'], "components.{$i}.updates.");
            }

            if (isset($component['calls'])) {
                foreach ($component['calls'] as $j => &$call) {
                    $call['params'] = $this->cleanArray($call['params'], "components.{$i}.calls.{$j}.params.");
                }
            }
        }

        $request->request->set('components', $components);
    }

    protected function isLivewireRequest(Request $request): bool
    {
        return $request->routeIs('*livewire.update');
    }
}
