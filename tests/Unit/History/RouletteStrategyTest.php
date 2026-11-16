<?php

namespace Tests\Unit\History;

use App\Modules\History\Exceptions\InvalidHistoryPayloadException;
use App\Modules\History\Strategies\RouletteStrategy;
use Tests\TestCase;

class RouletteStrategyTest extends TestCase
{
    private RouletteStrategy $strategy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->strategy = new RouletteStrategy();
    }

    public function test_module_name_is_roulette(): void
    {
        $this->assertSame('roulette', $this->strategy->moduleName());
    }

    public function test_validate_accepts_valid_spin(): void
    {
        $this->strategy->validate(
            ['options' => [
                ['label' => 'Ana', 'color' => '#ff0000'],
                ['label' => 'Beto', 'color' => '#00ff00'],
            ]],
            ['winner' => ['label' => 'Ana', 'color' => '#ff0000']],
        );

        $this->expectNotToPerformAssertions();
    }

    public function test_validate_rejects_empty_options(): void
    {
        try {
            $this->strategy->validate(
                ['options' => []],
                ['winner' => ['label' => 'x', 'color' => '#000']],
            );
            $this->fail('Expected InvalidHistoryPayloadException');
        } catch (InvalidHistoryPayloadException $e) {
            $this->assertArrayHasKey('payload.options', $e->errors());
        }
    }

    public function test_validate_rejects_option_without_label(): void
    {
        try {
            $this->strategy->validate(
                ['options' => [['color' => '#ff0000']]],
                ['winner' => ['label' => 'x', 'color' => '#000']],
            );
            $this->fail('Expected InvalidHistoryPayloadException');
        } catch (InvalidHistoryPayloadException $e) {
            $this->assertArrayHasKey('payload.options.0.label', $e->errors());
        }
    }

    public function test_validate_rejects_option_without_color(): void
    {
        try {
            $this->strategy->validate(
                ['options' => [['label' => 'Ana']]],
                ['winner' => ['label' => 'Ana', 'color' => '#000']],
            );
            $this->fail('Expected InvalidHistoryPayloadException');
        } catch (InvalidHistoryPayloadException $e) {
            $this->assertArrayHasKey('payload.options.0.color', $e->errors());
        }
    }

    public function test_validate_rejects_missing_winner(): void
    {
        try {
            $this->strategy->validate(
                ['options' => [['label' => 'Ana', 'color' => '#ff0000']]],
                [],
            );
            $this->fail('Expected InvalidHistoryPayloadException');
        } catch (InvalidHistoryPayloadException $e) {
            $this->assertArrayHasKey('result.winner', $e->errors());
        }
    }

    public function test_validate_rejects_winner_with_empty_label(): void
    {
        try {
            $this->strategy->validate(
                ['options' => [['label' => 'Ana', 'color' => '#ff0000']]],
                ['winner' => ['label' => '', 'color' => '#ff0000']],
            );
            $this->fail('Expected InvalidHistoryPayloadException');
        } catch (InvalidHistoryPayloadException $e) {
            $this->assertArrayHasKey('result.winner.label', $e->errors());
        }
    }

    public function test_validate_rejects_strict_types_and_empty_arrays(): void
    {
        $cases = [
            [['options' => []], ['winner' => ['label' => 'x', 'color' => '#000']], 'payload.options'],
            [['options' => [['label' => '', 'color' => '#000']]], ['winner' => ['label' => 'x', 'color' => '#000']], 'payload.options.0.label'],
            [['options' => [['label' => 'Ana', 'color' => '']]], ['winner' => ['label' => 'x', 'color' => '#000']], 'payload.options.0.color'],
        ];

        foreach ($cases as [$payload, $result, $key]) {
            try {
                $this->strategy->validate($payload, $result);
                $this->fail("Expected InvalidHistoryPayloadException for {$key}");
            } catch (InvalidHistoryPayloadException $e) {
                $this->assertArrayHasKey($key, $e->errors());
            }
        }
    }

    public function test_validate_reports_only_winner_key_when_missing(): void
    {
        try {
            $this->strategy->validate(
                ['options' => [['label' => 'Ana', 'color' => '#ff0000']]],
                [],
            );
            $this->fail('Expected InvalidHistoryPayloadException');
        } catch (InvalidHistoryPayloadException $e) {
            $this->assertSame(['result.winner'], array_keys($e->errors()));
        }
    }

    public function test_validate_reports_nested_keys_for_empty_winner(): void
    {
        try {
            $this->strategy->validate(
                ['options' => [['label' => 'Ana', 'color' => '#ff0000']]],
                ['winner' => []],
            );
            $this->fail('Expected InvalidHistoryPayloadException');
        } catch (InvalidHistoryPayloadException $e) {
            $keys = array_keys($e->errors());
            sort($keys);
            $this->assertSame(
                ['result.winner.color', 'result.winner.label'],
                $keys,
            );
        }
    }

    public function test_validate_reports_only_label_and_color_for_empty_option(): void
    {
        try {
            $this->strategy->validate(
                ['options' => [[]]],
                ['winner' => ['label' => 'x', 'color' => '#000']],
            );
            $this->fail('Expected InvalidHistoryPayloadException');
        } catch (InvalidHistoryPayloadException $e) {
            $keys = array_keys($e->errors());
            sort($keys);
            $this->assertSame(
                ['payload.options.0.color', 'payload.options.0.label'],
                $keys,
            );
        }
    }

    public function test_validate_reports_only_option_key_for_scalar_option(): void
    {
        try {
            $this->strategy->validate(
                ['options' => ['x']],
                ['winner' => ['label' => 'x', 'color' => '#000']],
            );
            $this->fail('Expected InvalidHistoryPayloadException');
        } catch (InvalidHistoryPayloadException $e) {
            $this->assertSame(['payload.options.0'], array_keys($e->errors()));
        }
    }
}
