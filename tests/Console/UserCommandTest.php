<?php

namespace Canvas\Tests\Console;

use Canvas\Models\User;
use Canvas\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Class AdminCommandTest.
 *
 * @covers \Canvas\Console\UserCommand
 */
class UserCommandTest extends TestCase
{
    use RefreshDatabase;

    public function testCanvasUserCommandWillValidateAnEmptyEmail(): void
    {
        $this->artisan('blog:user admin')
             ->assertExitCode(0)
             ->expectsOutput('Please enter a valid email.');
    }

    public function testCanvasUserCommandWillValidateAnInvalidEmail(): void
    {
        $this->artisan('blog:user admin --email bad.email')
             ->assertExitCode(0)
             ->expectsOutput('Please enter a valid email.');
    }

    public function testCanvasUserCommandWillValidateAnInvalidRole(): void
    {
        $this->artisan('blog:user ad --email email@example.com')
             ->assertExitCode(0)
             ->expectsOutput('Please enter a valid role.');
    }

    public function testCanvasUserCommandCanCreateANewContributor(): void
    {
        $this->artisan('blog:user contributor --email contributor@example.com')
             ->assertExitCode(0)
             ->expectsOutput('New user created.');

        $this->assertDatabaseHas('blog_users', [
            'email' => 'contributor@example.com',
            'role' => User::CONTRIBUTOR,
        ]);
    }

    public function testCanvasUserCommandCanCreateANewEditor(): void
    {
        $this->artisan('blog:user editor --email editor@example.com')
             ->assertExitCode(0)
             ->expectsOutput('New user created.');

        $this->assertDatabaseHas('blog_users', [
            'email' => 'editor@example.com',
            'role' => User::EDITOR,
        ]);
    }

    public function testCanvasUserCommandCanCreateANewAdmin(): void
    {
        $this->artisan('blog:user admin --email admin@example.com')
             ->assertExitCode(0)
             ->expectsOutput('New user created.');

        $this->assertDatabaseHas('blog_users', [
            'email' => 'admin@example.com',
            'role' => User::ADMIN,
        ]);
    }
}
