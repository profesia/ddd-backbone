<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Infrastructure\Utils\Backtrace;

trait FormatsBacktrace
{
    public static function formatBacktrace(array $backtrace): array
    {
        return array_map(
            static function (array $item): array {
                $modifiedItem = [
                    'function' => $item['function'],
                ];

                if (isset($item['line'])) {
                    $modifiedItem['line'] = $item['line'];
                }

                if (isset($item['file'])) {
                    $modifiedItem['file'] = $item['file'];
                }

                if (isset($item['class'])) {
                    $modifiedItem['class'] = $item['class'];
                }

                return $modifiedItem;
            },
            $backtrace
        );
    }
}
