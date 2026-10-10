<?php

declare(strict_types=1);

namespace Pikachu\Challenge;

use Pikachu\Http\Request;
use Pikachu\Http\Response;

/**
 * Standard Contract for all Cybersecurity Challenges in Pikachu-Enhanced.
 */
interface ChallengeInterface
{
    /**
     * Unique identifier (e.g. "sqli_str", "osep_l1_enum").
     */
    public function getId(): string;

    /**
     * Display title of the challenge.
     */
    public function getTitle(): string;

    /**
     * Category (e.g. "sqli", "osep", "oswe", "ai_security").
     */
    public function getCategory(): string;

    /**
     * Difficulty level: "Beginner", "Intermediate", "Advanced", "Expert".
     */
    public function getDifficulty(): string;

    /**
     * OWASP Top 10 or MITRE ATT&CK mapping.
     */
    public function getOwaspCategory(): string;

    /**
     * Points awarded for completing the challenge.
     */
    public function getPoints(): int;

    /**
     * Educational description and principles.
     */
    public function getDescription(): string;

    /**
     * Educational walkthrough hints.
     *
     * @return array<int, string>
     */
    public function getHints(): array;

    /**
     * Defense and secure coding remediation advice.
     */
    public function getMitigation(): string;

    /**
     * Validate a user-submitted flag.
     */
    public function verifyFlag(string $flag): bool;

    /**
     * Handle HTTP request for this challenge.
     */
    public function handle(Request $request): Response;
}
