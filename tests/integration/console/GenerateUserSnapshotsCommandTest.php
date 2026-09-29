<?php

/*
 * This file is part of huseyinfiliz/rewind.
 *
 * Copyright (c) 2026 Hüseyin Filiz.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace HuseyinFiliz\Rewind\Tests\integration\console;

use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use HuseyinFiliz\Rewind\Console\GenerateUserSnapshotsCommand;
use HuseyinFiliz\Rewind\Model\RewindSnapshot;
use Symfony\Component\Console\Tester\CommandTester;

class GenerateUserSnapshotsCommandTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extension('huseyinfiliz-rewind');
        $this->setting('huseyinfiliz-rewind.enabled', true);
        $this->setting('huseyinfiliz-rewind.active_year', 2025);

        $this->prepareDatabase([
            'users' => [
                $this->normalUser(),
                ['id' => 3, 'username' => 'alice', 'email' => 'alice@machine.local', 'password' => 'test-password', 'is_email_confirmed' => 1],
            ],
            'discussions' => [
                ['id' => 1, 'title' => 'Discussion One', 'slug' => 'discussion-one', 'user_id' => 2, 'created_at' => '2025-04-01 10:00:00', 'comment_count' => 1, 'first_post_id' => 1, 'last_post_number' => 1],
            ],
            'posts' => [
                ['id' => 1, 'discussion_id' => 1, 'number' => 1, 'user_id' => 2, 'type' => 'comment', 'created_at' => '2025-04-01 10:00:00', 'content' => '<t><p>First post content</p></t>'],
            ],
        ]);
    }

    /** @test */
    public function command_generates_user_snapshots()
    {
        /** @var GenerateUserSnapshotsCommand $command */
        $command = $this->app()->getContainer()->make(GenerateUserSnapshotsCommand::class);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute(['year' => 2025, '--force' => true]);

        $this->assertEquals(0, $exitCode);
        $this->assertStringContainsString('Successfully generated', $tester->getDisplay());

        $snapshot = RewindSnapshot::where('year', 2025)->where('user_id', 2)->first();
        $this->assertNotNull($snapshot);
        $this->assertNotNull($snapshot->data);
    }

    /** @test */
    public function command_generates_for_specific_user()
    {
        /** @var GenerateUserSnapshotsCommand $command */
        $command = $this->app()->getContainer()->make(GenerateUserSnapshotsCommand::class);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute(['year' => 2025, '--user' => 'alice', '--force' => true]);

        $this->assertEquals(0, $exitCode);
        $this->assertStringContainsString('Successfully generated 1 user rewind snapshot', $tester->getDisplay());

        $aliceSnapshot = RewindSnapshot::where('year', 2025)->where('user_id', 3)->first();
        $this->assertNotNull($aliceSnapshot);
    }
}
