<?php

namespace Tests\Feature\Api\V1\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_list_users_with_pagination(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        User::factory()->count(15)->create();

        Sanctum::actingAs($superAdmin);

        $response = $this->getJson('/api/v1/admin/users');

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'meta' => [
                    'current_page',
                    'last_page',
                ],
            ])
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonCount(10, 'data');
    }

    public function test_management_department_user_can_list_users(): void
    {
        $manager = User::factory()->management()->create();

        Sanctum::actingAs($manager);

        $response = $this->getJson('/api/v1/admin/users');

        $response->assertOk();
    }

    public function test_regular_user_cannot_list_users_and_receives_403(): void
    {
        $staffUser = User::factory()->department('store_staff')->create([
            'is_super_admin' => false,
        ]);

        Sanctum::actingAs($staffUser);

        $response = $this->getJson('/api/v1/admin/users');

        $response->assertForbidden()
            ->assertJsonPath('message', 'Unauthorized attributes.');
    }

    public function test_unauthenticated_request_to_list_users_returns_401(): void
    {
        $response = $this->getJson('/api/v1/admin/users');

        $response->assertUnauthorized();
    }

    public function test_super_admin_can_update_user_attributes(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $targetUser = User::factory()->department('general')->forStore(1)->create([
            'is_super_admin' => false,
        ]);

        Sanctum::actingAs($superAdmin);

        $response = $this->putJson("/api/v1/admin/users/{$targetUser->id}/attributes", [
            'department' => 'finance',
            'store_id' => 5,
            'is_super_admin' => true,
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'User attributes updated successfully')
            ->assertJsonPath('user.department', 'finance')
            ->assertJsonPath('user.store_id', 5)
            ->assertJsonPath('user.is_super_admin', true);

        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'department' => 'finance',
            'store_id' => 5,
            'is_super_admin' => 1,
        ]);
    }

    public function test_non_super_admin_cannot_update_user_attributes_and_receives_403(): void
    {
        $manager = User::factory()->management()->create([
            'is_super_admin' => false,
        ]);
        $targetUser = User::factory()->department('general')->forStore(1)->create();

        Sanctum::actingAs($manager);

        $response = $this->putJson("/api/v1/admin/users/{$targetUser->id}/attributes", [
            'department' => 'finance',
            'store_id' => 5,
            'is_super_admin' => true,
        ]);

        $response->assertForbidden()
            ->assertJsonPath('message', 'Only super admins can modify user attributes/roles.');

        $this->assertDatabaseMissing('users', [
            'id' => $targetUser->id,
            'department' => 'finance',
        ]);
    }

    public function test_update_attributes_fails_validation_when_required_fields_are_missing(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $targetUser = User::factory()->create();

        Sanctum::actingAs($superAdmin);

        $response = $this->putJson("/api/v1/admin/users/{$targetUser->id}/attributes", []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['department', 'store_id', 'is_super_admin']);
    }

    public function test_update_attributes_fails_validation_with_invalid_data_types(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $targetUser = User::factory()->create();

        Sanctum::actingAs($superAdmin);

        $response = $this->putJson("/api/v1/admin/users/{$targetUser->id}/attributes", [
            'department' => ['invalid-array'],
            'store_id' => 'not-an-integer',
            'is_super_admin' => 'not-a-boolean',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['department', 'store_id', 'is_super_admin']);
    }

    public function test_unauthenticated_request_to_update_attributes_returns_401(): void
    {
        $targetUser = User::factory()->create();

        $response = $this->putJson("/api/v1/admin/users/{$targetUser->id}/attributes", [
            'department' => 'finance',
            'store_id' => 1,
            'is_super_admin' => false,
        ]);

        $response->assertUnauthorized();
    }

    public function test_updating_attributes_of_nonexistent_user_returns_404(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        Sanctum::actingAs($superAdmin);

        $response = $this->putJson('/api/v1/admin/users/99999/attributes', [
            'department' => 'finance',
            'store_id' => 1,
            'is_super_admin' => false,
        ]);

        $response->assertNotFound();
    }
}
