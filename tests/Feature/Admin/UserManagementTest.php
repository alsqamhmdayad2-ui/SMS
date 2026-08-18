<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $this->admin->assignRole($adminRole);
    }

    // ──────────────────────────────────────────────────────────
    // عرض قائمة المستخدمين
    // ──────────────────────────────────────────────────────────

    public function test_admin_can_view_users_list()
    {
        User::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertViewIs('panels.admin.users.index');
        $response->assertViewHas('users');
        $response->assertViewHas('roles');
    }

    public function test_admin_can_search_users_by_name()
    {
        User::factory()->create(['name' => 'Ahmed Ali']);
        User::factory()->create(['name' => 'Mohammed Saad']);

        $response = $this->actingAs($this->admin)->get(route('admin.users.index', ['search' => 'Ahmed']));

        $response->assertStatus(200);
        $response->assertSee('Ahmed Ali');
    }

    public function test_admin_can_filter_users_by_role()
    {
        $teacherRole = Role::firstOrCreate(['name' => 'teacher']);
        $teacher = User::factory()->create(['name' => 'Teacher User']);
        $teacher->assignRole($teacherRole);

        $response = $this->actingAs($this->admin)->get(route('admin.users.index', ['role' => 'teacher']));

        $response->assertStatus(200);
        $response->assertSee('Teacher User');
    }

    // ──────────────────────────────────────────────────────────
    // إضافة مستخدم
    // ──────────────────────────────────────────────────────────

    public function test_admin_can_view_create_user_form()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.create'));

        $response->assertStatus(200);
        $response->assertViewIs('panels.admin.users.create');
        $response->assertViewHas('roles');
    }

    public function test_admin_can_store_new_user()
    {
        Role::firstOrCreate(['name' => 'teacher']);

        $data = [
            'name' => 'New Teacher',
            'email' => 'teacher@school.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'teacher',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), $data);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'name' => 'New Teacher',
            'email' => 'teacher@school.com',
        ]);

        $newUser = User::where('email', 'teacher@school.com')->first();
        $this->assertTrue($newUser->hasRole('teacher'));
    }

    public function test_store_user_requires_name_and_password()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), []);

        $response->assertSessionHasErrors(['name', 'password', 'role']);
    }

    public function test_store_user_requires_unique_email()
    {
        $existingUser = User::factory()->create(['email' => 'taken@school.com']);
        Role::firstOrCreate(['name' => 'teacher']);

        $data = [
            'name' => 'Duplicate',
            'email' => 'taken@school.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'teacher',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), $data);

        $response->assertSessionHasErrors('email');
    }

    // ──────────────────────────────────────────────────────────
    // تعديل مستخدم
    // ──────────────────────────────────────────────────────────

    public function test_admin_can_view_edit_user_form()
    {
        $teacherRole = Role::firstOrCreate(['name' => 'teacher']);
        $user = User::factory()->create();
        $user->assignRole($teacherRole);

        $response = $this->actingAs($this->admin)->get(route('admin.users.edit', $user));

        $response->assertStatus(200);
        $response->assertViewIs('panels.admin.users.edit');
        $response->assertViewHas('user');
        $response->assertViewHas('roles');
        $response->assertViewHas('userRole');
    }

    public function test_admin_can_update_user_name_and_role()
    {
        $teacherRole = Role::firstOrCreate(['name' => 'teacher']);
        $parentRole = Role::firstOrCreate(['name' => 'parent']);
        $user = User::factory()->create(['name' => 'Old Name']);
        $user->assignRole($teacherRole);

        $data = [
            'name' => 'New Name',
            'email' => $user->email,
            'role' => 'parent',
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.users.update', $user), $data);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'New Name']);
        $this->assertTrue($user->fresh()->hasRole('parent'));
    }

    public function test_admin_can_update_user_password()
    {
        $teacherRole = Role::firstOrCreate(['name' => 'teacher']);
        $user = User::factory()->create();
        $user->assignRole($teacherRole);

        $data = [
            'name' => $user->name,
            'email' => $user->email,
            'role' => 'teacher',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.users.update', $user), $data);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertTrue(Hash::check('NewPassword123!', $user->fresh()->password));
    }

    public function test_update_user_requires_unique_email()
    {
        $teacherRole = Role::firstOrCreate(['name' => 'teacher']);
        $user1 = User::factory()->create(['email' => 'user1@school.com']);
        $user2 = User::factory()->create(['email' => 'user2@school.com']);
        $user1->assignRole($teacherRole);

        $data = [
            'name' => $user1->name,
            'email' => 'user2@school.com', // already taken
            'role' => 'teacher',
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.users.update', $user1), $data);

        $response->assertSessionHasErrors('email');
    }

    // ──────────────────────────────────────────────────────────
    // حذف مستخدم
    // ──────────────────────────────────────────────────────────

    public function test_admin_can_delete_another_user()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $user));

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_admin_cannot_delete_themselves()
    {
        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $this->admin));

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
        $response->assertSessionHas('error');
    }

    // ──────────────────────────────────────────────────────────
    // صلاحيات - يمنع الوصول لغير الأدمن
    // ──────────────────────────────────────────────────────────

    public function test_non_admin_cannot_access_users_list()
    {
        $teacherRole = Role::firstOrCreate(['name' => 'teacher']);
        $teacher = User::factory()->create();
        $teacher->assignRole($teacherRole);

        $response = $this->actingAs($teacher)->get(route('admin.users.index'));

        $response->assertStatus(403);
    }

    public function test_guest_cannot_access_users_list()
    {
        $response = $this->get(route('admin.users.index'));

        $response->assertRedirect(route('login'));
    }
}
