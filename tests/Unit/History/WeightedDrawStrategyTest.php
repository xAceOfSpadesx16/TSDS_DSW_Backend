<?php

namespace Tests\Unit\History;

use App\Modules\History\Exceptions\InvalidHistoryPayloadException;
use App\Modules\History\Strategies\WeightedDrawStrategy;
use Tests\TestCase;

class WeightedDrawStrategyTest extends TestCase
{
    private WeightedDrawStrategy $strategy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->strategy = new WeightedDrawStrategy();
    }

    public function test_module_name_is_weighted(): void
    {
        $this->assertSame('weighted', $this->strategy->moduleName());
    }

    public function test_validate_accepts_standard_draw(): void
    {
        $this->strategy->validate(
            [
                'entries' => [
                    ['name' => 'Ana', 'weight' => 5],
                    ['name' => 'Beto', 'weight' => 3],
                ],
                'autoRemove' => false,
            ],
            [
                'winner' => ['name' => 'Ana', 'weight' => 5],
                'probability' => 62.5,
            ],
        );

        $this->expectNotToPerformAssertions();
    }

    public function test_validate_accepts_float_weights(): void
    {
        $this->strategy->validate(
            ['entries' => [['name' => 'Ana', 'weight' => 1.5]]],
            ['winner' => ['name' => 'Ana', 'weight' => 1.5], 'probability' => 100.0],
        );

        $this->expectNotToPerformAssertions();
    }

    public function test_validate_rejects_empty_entries(): void
    {
        try {
            $this->strategy->validate(
                ['entries' => []],
                ['winner' => ['name' => 'Ana', 'weight' => 1], 'probability' => 100],
            );
            $this->fail('Expected InvalidHistoryPayloadException');
        } catch (InvalidHistoryPayloadException $e) {
            $this->assertArrayHasKey('payload.entries', $e->errors());
        }
    }

    public function test_validate_rejects_entry_with_zero_weight(): void
    {
        try {
            $this->strategy->validate(
                ['entries' => [['name' => 'Ana', 'weight' => 0]]],
                ['winner' => ['name' => 'Ana', 'weight' => 0], 'probability' => 100],
            );
            $this->fail('Expected InvalidHistoryPayloadException');
        } catch (InvalidHistoryPayloadException $e) {
            $this->assertArrayHasKey('payload.entries.0.weight', $e->errors());
        }
    }

    public function test_validate_rejects_entry_without_name(): void
    {
        try {
            $this->strategy->validate(
                ['entries' => [['weight' => 5]]],
                ['winner' => ['name' => 'Ana', 'weight' => 5], 'probability' => 100],
            );
            $this->fail('Expected InvalidHistoryPayloadException');
        } catch (InvalidHistoryPayloadException $e) {
            $this->assertArrayHasKey('payload.entries.0.name', $e->errors());
        }
    }

    public function test_validate_rejects_non_numeric_weight(): void
    {
        try {
            $this->strategy->validate(
                ['entries' => [['name' => 'Ana', 'weight' => 'heavy']]],
                ['winner' => ['name' => 'Ana', 'weight' => 'heavy'], 'probability' => 100],
            );
            $this->fail('Expected InvalidHistoryPayloadException');
        } catch (InvalidHistoryPayloadException $e) {
            $this->assertArrayHasKey('payload.entries.0.weight', $e->errors());
        }
    }

    public function test_validate_rejects_probability_out_of_range(): void
    {
        try {
            $this->strategy->validate(
                ['entries' => [['name' => 'Ana', 'weight' => 5]]],
                ['winner' => ['name' => 'Ana', 'weight' => 5], 'probability' => 150],
            );
            $this->fail('Expected InvalidHistoryPayloadException');
        } catch (InvalidHistoryPayloadException $e) {
            $this->assertArrayHasKey('result.probability', $e->errors());
        }
    }

    public function test_validate_rejects_non_boolean_auto_remove(): void
    {
        try {
            $this->strategy->validate(
                [
                    'entries' => [['name' => 'Ana', 'weight' => 5]],
                    'autoRemove' => 'yes',
                ],
                ['winner' => ['name' => 'Ana', 'weight' => 5], 'probability' => 100],
            );
            $this->fail('Expected InvalidHistoryPayloadException');
        } catch (InvalidHistoryPayloadException $e) {
            $this->assertArrayHasKey('payload.autoRemove', $e->errors());
        }
    }

    public function test_validate_rejects_strict_types_and_empty_arrays(): void
    {
        $cases = [
            [['entries' => [['name' => 'Ana', 'weight' => '5']]], ['winner' => ['name' => 'Ana', 'weight' => 5], 'probability' => 100], 'payload.entries.0.weight'],
            [['entries' => [['name' => 'Ana', 'weight' => 5]], 'autoRemove' => 'true'], ['winner' => ['name' => 'Ana', 'weight' => 5], 'probability' => 100], 'payload.autoRemove'],
            [['entries' => []], ['winner' => ['name' => 'Ana', 'weight' => 5], 'probability' => 100], 'payload.entries'],
            [['entries' => [['name' => 'Ana', 'weight' => 5]]], ['winner' => ['name' => 'Ana', 'weight' => '5'], 'probability' => 100], 'result.winner.weight'],
            [['entries' => [['name' => 'Ana', 'weight' => 5]]], ['winner' => ['name' => 'Ana', 'weight' => 5], 'probability' => '100'], 'result.probability'],
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
                ['entries' => [['name' => 'Ana', 'weight' => 5]]],
                ['probability' => 100],
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
                ['entries' => [['name' => 'Ana', 'weight' => 5]]],
                ['winner' => [], 'probability' => 100],
            );
            $this->fail('Expected InvalidHistoryPayloadException');
        } catch (InvalidHistoryPayloadException $e) {
            $keys = array_keys($e->errors());
            sort($keys);
            $this->assertSame(
                ['result.winner.name', 'result.winner.weight'],
                $keys,
            );
        }
    }

    public function test_validate_reports_only_entry_key_for_scalar_entry(): void
    {
        try {
            $this->strategy->validate(
                ['entries' => ['x']],
                ['winner' => ['name' => 'Ana', 'weight' => 5], 'probability' => 100],
            );
            $this->fail('Expected InvalidHistoryPayloadException');
        } catch (InvalidHistoryPayloadException $e) {
            $this->assertSame(['payload.entries.0'], array_keys($e->errors()));
        }
    }

    public function test_validate_reports_only_name_and_weight_for_empty_entry(): void
    {
        try {
            $this->strategy->validate(
                ['entries' => [[]]],
                ['winner' => ['name' => 'Ana', 'weight' => 5], 'probability' => 100],
            );
            $this->fail('Expected InvalidHistoryPayloadException');
        } catch (InvalidHistoryPayloadException $e) {
            $keys = array_keys($e->errors());
            sort($keys);
            $this->assertSame(
                ['payload.entries.0.name', 'payload.entries.0.weight'],
                $keys,
            );
        }
    }
}
