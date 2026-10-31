<?php

namespace HuseyinFiliz\Rewind\Tests\integration\api;

use Carbon\Carbon;
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use HuseyinFiliz\Rewind\Model\RewindSnapshot;

class DeleteSnapshotTest extends TestCase
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
                ['id' => 3, 'username' => 'mod', 'email' => 'mod@machine.local', 'password' => 'test-password', 'is_email_confirmed' => 1],
            ],
            'group_user' => [
                ['user_id' => 3, 'group_id' => 4],
            ],
            'group_permission' => [
                ['group_id' => 3, 'permission' => 'huseyinfiliz-rewind.viewForum'],
                ['group_id' => 3, 'permission' => 'huseyinfiliz-rewind.generate'],
                ['group_id' => 4, 'permission' => 'huseyinfiliz-rewind.moderate'],
            ],
            'rw_snaps' => [
                ['id' => 1, 'user_id' => 2, 'year' => 2025, 'data' => json_encode(['post_count' => ['count' => 10]]), 'generated_at' => Carbon::now()->toDateTimeString(), 'is_public' => true],
            ],
        ]);
    }

    /** @test */
    public function owner_cannot_delete_own_snapshot()
    {
        $response = $this->send(
            $this->request('DELETE', '/api/rw-snaps/1', ['authenticatedAs' => 2])
        );

        $this->assertEquals(403, $response->getStatusCode());
    }

    /** @test */
    public function moderator_can_delete()
    {
        $response = $this->send(
            $this->request('DELETE', '/api/rw-snaps/1', ['authenticatedAs' => 3])
        );

        $this->assertEquals(204, $response->getStatusCode());
    }

    /** @test */
    public function admin_can_delete()
    {
        $response = $this->send(
            $this->request('DELETE', '/api/rw-snaps/1', ['authenticatedAs' => 1])
        );

        $this->assertEquals(204, $response->getStatusCode());
    }

    /** @test */
    public function non_admin_cannot_batch_delete()
    {
        $response = $this->send(
            $this->request('POST', '/api/rw-snaps/batch-delete', [
                'authenticatedAs' => 3,
                'json' => ['year' => 2025],
            ])
        );

        $this->assertEquals(403, $response->getStatusCode());
    }

    /** @test */
    public function admin_can_batch_delete_by_group()
    {
        $this->prepareDatabase([
            'groups' => [
                ['id' => 5, 'name_singular' => 'Test', 'name_plural' => 'Tests'],
            ],
            'users' => [
                ['id' => 10, 'username' => 'grouped', 'email' => 'grouped@machine.local', 'password' => 'test-password', 'is_email_confirmed' => 1],
                ['id' => 11, 'username' => 'other', 'email' => 'other@machine.local', 'password' => 'test-password', 'is_email_confirmed' => 1],
            ],
            'group_user' => [
                ['user_id' => 10, 'group_id' => 5],
            ],
            'rw_snaps' => [
                ['id' => 10, 'user_id' => 10, 'year' => 2025, 'data' => json_encode(['post_count' => ['count' => 1]]), 'generated_at' => Carbon::now()->toDateTimeString(), 'is_public' => true],
                ['id' => 11, 'user_id' => 11, 'year' => 2025, 'data' => json_encode(['post_count' => ['count' => 2]]), 'generated_at' => Carbon::now()->toDateTimeString(), 'is_public' => true],
            ],
        ]);

        $response = $this->send(
            $this->request('POST', '/api/rw-snaps/batch-delete', [
                'authenticatedAs' => 1,
                'json' => ['year' => 2025, 'group' => 5],
            ])
        );

        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertEquals(1, $body['deleted']);
        $this->assertNull(RewindSnapshot::find(10));
        $this->assertNotNull(RewindSnapshot::find(11));
    }
}
