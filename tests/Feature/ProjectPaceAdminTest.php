<?php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProjectPaceAdminTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Never run migrations or tests on the real catalog database.
        if (!$this->app->environment('testing')) {
            throw new \RuntimeException('Testing environment required.');
        }
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.url' => null,
            'session.driver' => 'array',
            'cache.default' => 'array',
        ]);
        DB::purge('sqlite');
        $this->artisan('migrate', ['--database' => 'sqlite', '--force' => true])->assertExitCode(0);
    }

    private function account(bool $admin): User
    {
        $user = new User();
        $user->name = 'Test';
        $user->email = ($admin ? 'admin' : 'customer').'@example.test';
        $user->password = Hash::make('Test-password-123');
        $user->is_admin = $admin;
        $user->save();
        return $user;
    }

    public function test_guests_cannot_access_dashboard(): void
    {
        $this->get('/admin')->assertRedirect(route('login'));
        $this->get('/admin/login')->assertOk();
    }

    public function test_non_admin_cannot_access_dashboard_even_if_authenticated(): void
    {
        $this->actingAs($this->account(false))->get('/admin')->assertForbidden();
    }

    public function test_admin_can_login_view_dashboard_and_logout(): void
    {
        $admin = $this->account(true);
        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'Test-password-123'])
            ->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
        $this->get('/admin')->assertOk()->assertSee('DASHBOARD.')->assertSee('Catálogo registrado');
        $this->post('/admin/logout')->assertRedirect(route('login'));
        $this->assertGuest();
        $this->get('/admin')->assertRedirect(route('login'));
    }

    public function test_non_admin_credentials_do_not_grant_login(): void
    {
        $user = $this->account(false);
        $this->from('/admin/login')->post('/admin/login', ['email' => $user->email, 'password' => 'Test-password-123'])
            ->assertRedirect('/admin/login')->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_repeated_invalid_attempts_are_limited(): void
    {
        $admin = $this->account(true);
        for ($i = 0; $i < 5; $i++) {
            $this->from('/admin/login')->post('/admin/login', ['email' => $admin->email, 'password' => 'incorrect'])
                ->assertSessionHasErrors('email');
        }
        $this->from('/admin/login')->post('/admin/login', ['email' => $admin->email, 'password' => 'Test-password-123'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
