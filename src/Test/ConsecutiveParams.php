<?php

declare(strict_types=1);

namespace Factfinder\Export\Test;

use InvalidArgumentException;

trait ConsecutiveParams
{
    // @see: https://stackoverflow.com/questions/75389000/replace-phpunit-method-withconsecutive
    public function consecutiveParams(array ...$args): array
    {
        $callbacks = [];
        $count = count(max($args));

        for ($index = 0; $index < $count; $index++) {
            $returns = [];

            foreach ($args as $arg) {
                if (!array_is_list($arg)) {
                    throw new InvalidArgumentException('Every array must be a list');
                }

                if (!isset($arg[$index])) {
                    throw new InvalidArgumentException(sprintf('Every array must contain %d parameters', $count));
                }

                $returns[] = $arg[$index];
            }

            $callbacks[] = $this->callback(new class ($returns) {
                public function __construct(protected array $returns)
                {
                }

                public function __invoke(mixed $actual): bool
                {
                    if (count($this->returns) === 0) {
                        return true;
                    }

                    return $actual === array_shift($this->returns);
                }
            });
        }

        return $callbacks;
    }
}