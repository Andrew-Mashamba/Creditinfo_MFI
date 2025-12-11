<?php

namespace App\Logging;

use Monolog\Processor\ProcessorInterface;

class DebugTraceProcessor implements ProcessorInterface
{
    public function __invoke(array $record): array
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
        foreach ($trace as $item) {
            if (isset($item['file']) && strpos($item['file'], 'vendor') === false) {
                $record['extra']['file'] = str_replace(base_path(), '', $item['file']);
                $record['extra']['line'] = $item['line'] ?? '';
                $record['extra']['function'] = $item['function'] ?? '';
                break;
            }
        }
        return $record;
    }
}