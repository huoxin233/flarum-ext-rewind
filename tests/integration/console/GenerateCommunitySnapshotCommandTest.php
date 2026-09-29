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
use HuseyinFiliz\Rewind\Console\GenerateCommunitySnapshotCommand;
use HuseyinFiliz\Rewind\Model\CommunitySnapshot;
use Symfony\Component\Console\Tester\CommandTester;

class GenerateCommunitySnapshotCommandTest extends TestCase
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
    public function command_generates_community_snapshot()
    {
        /** @var GenerateCommunitySnapshotCommand $command */
        $command = $this->app()->getContainer()->make(GenerateCommunitySnapshotCommand::class);
        $tester = new CommandTester($command);

        $exitCode = $tester->execute(['year' => 2025, '--force' => true]);

        $this->assertEquals(0, $exitCode);
        $this->assertStringContainsString('successfully generated', $tester->getDisplay());

        $snapshot = CommunitySnapshot::where('year', 2025)->first();
        $this->assertNotNull($snapshot);
        $this->assertNotNull($snapshot->data);
        $this->assertEquals(1, $snapshot->data['total_posts']['count'] ?? null);
    }
}
