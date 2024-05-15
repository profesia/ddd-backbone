<?php

declare(strict_types=1);

namespace Profesia\DddBackbone\Infrastructure\Utils\Backtrace;

trait FormatsBacktrace
{
    /**
     * @param mixed[] $backtrace
     * @return array<array<string, mixed>>
     */
    public static function formatBacktrace(array $backtrace): array
    {
        return array_map(
            /**
             * @phpstan-ignore-next-line 
             */
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
