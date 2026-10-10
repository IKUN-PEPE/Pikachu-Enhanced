<?php

declare(strict_types=1);

namespace Pikachu\Challenge;

use Pikachu\Http\Request;
use Pikachu\Http\Response;

/**
 * Base Abstract Challenge providing common boilerplate and flag checking.
 */
abstract class AbstractChallenge implements ChallengeInterface
{
    protected string $id;
    protected string $title;
    protected string $category;
    protected string $difficulty = 'Intermediate';
    protected string $owaspCategory = 'OWASP Top 10';
    protected int $points = 100;
    protected string $description = '';
    /** @var array<int, string> */
    protected array $hints = [];
    protected string $mitigation = '';
    protected string $expectedFlag = '';

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getDifficulty(): string
    {
        return $this->difficulty;
    }

    public function getOwaspCategory(): string
    {
        return $this->owaspCategory;
    }

    public function getPoints(): int
    {
        return $this->points;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getHints(): array
    {
        return $this->hints;
    }

    public function getMitigation(): string
    {
        return $this->mitigation;
    }

    public function verifyFlag(string $flag): bool
    {
        if (empty($this->expectedFlag)) {
            return false;
        }
        return hash_equals($this->expectedFlag, trim($flag));
    }

    public function handle(Request $request): Response
    {
        if ($request->isPost() && $request->has('check_flag')) {
            $submitted = (string) $request->post('flag', '');
            $passed = $this->verifyFlag($submitted);

            return Response::json([
                'success' => $passed,
                'message' => $passed ? '恭喜通关！Flag 验证正确！' : 'Flag 验证失败，请仔细复核利用链路！',
                'points' => $passed ? $this->points : 0,
            ]);
        }

        return Response::html("<h1>{$this->title}</h1><p>{$this->description}</p>");
    }
}
