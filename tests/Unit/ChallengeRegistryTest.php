<?php

declare(strict_types=1);

namespace Pikachu\Tests\Unit;

use Pikachu\Challenge\ChallengeRegistry;
use Pikachu\Challenge\AbstractChallenge;

class DummyChallenge extends AbstractChallenge
{
    public function __construct()
    {
        $this->id = 'test_challenge_01';
        $this->title = 'Test Challenge';
        $this->category = 'sqli';
        $this->expectedFlag = 'flag{dummy_test}';
    }
}

class ChallengeRegistryTest
{
    public function testRegistryCategories(): bool
    {
        $registry = new ChallengeRegistry();
        $cats = $registry->getCategories();

        assert(isset($cats['classic']), 'classic category missing');
        assert(isset($cats['cloud']), 'cloud category missing');
        assert(isset($cats['osep']), 'osep category missing');
        assert(isset($cats['oswe']), 'oswe category missing');
        assert(isset($cats['osed']), 'osed category missing');
        assert(count($cats) >= 10, 'Should have at least 10 major category definitions');
        return true;
    }

    public function testRegisterAndRetrieve(): bool
    {
        $registry = new ChallengeRegistry();
        $challenge = new DummyChallenge();

        $registry->register($challenge);
        assert($registry->count() === 1, 'Registry count mismatch');
        assert($registry->get('test_challenge_01') !== null, 'Retrieve challenge failed');
        assert($challenge->verifyFlag('flag{dummy_test}'), 'Challenge flag verification failed');
        return true;
    }
}
