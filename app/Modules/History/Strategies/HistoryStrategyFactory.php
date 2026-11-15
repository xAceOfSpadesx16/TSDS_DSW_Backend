<?php

namespace App\Modules\History\Strategies;

use App\Modules\History\Exceptions\UnsupportedModuleException;

/** Resuelve la estrategia de validación de cada módulo. */
class HistoryStrategyFactory
{
    /**
     * @var array<string, class-string<HistoryStrategyInterface>>
     */
    private const STRATEGIES = [
        'dice' => DiceRollerStrategy::class,
        'numbers' => NumberGeneratorStrategy::class,
        'roulette' => RouletteStrategy::class,
        'teams' => TeamSplitterStrategy::class,
        'weighted' => WeightedDrawStrategy::class,
    ];

    /**
     * Resuelve la estrategia del módulo.
     *
     * @throws UnsupportedModuleException
     */
    public function make(string $moduleName): HistoryStrategyInterface
    {
        if (! isset(self::STRATEGIES[$moduleName])) {
            throw UnsupportedModuleException::forModule($moduleName);
        }

        /** @var HistoryStrategyInterface $strategy */
        $strategy = app(self::STRATEGIES[$moduleName]);

        return $strategy;
    }

    /**
     * Módulos soportados.
     *
     * @return array<int, string>
     */
    public function supportedModules(): array
    {
        return array_keys(self::STRATEGIES);
    }
}