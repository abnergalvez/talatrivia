<?php

namespace Tests\Feature\User;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;

class UserAdminTest extends TestCase
{
    protected $adminUser;
    protected $playerUser;
    protected $adminRole;
    protected $playerRole;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('migrate:fresh');
        $this->adminRole = Role::create(['name' => 'admin']);
        $this->playerRole = Role::create(['name' => 'player']);
        
        $this->adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => Hash::make('admin123'),
            'api_token' => 'admin-token-123',
            'role_id' => $this->adminRole->id,
        ]);
        $this->playerUser = User::create([
            'name' => 'Player User',
            'email' => 'player@test.com',
            'password' => Hash::make('player123'),
            'api_token' => 'player-token-123',
            'role_id' => $this->playerRole->id,
        ]);
    }

    public function test_admin_can_list_all_users()
    {
        $response = $this->get('/api/users', [
            'Authorization' => 'Bearer ' . $this->adminUser->api_token
        ]);

        $response->seeStatusCode(200);
        $data = json_decode($response->response->getContent(), true);
        $this->assertTrue(is_array($data));
        $this->assertGreaterThan(0, count($data));
    }

    public function test_player_cannot_list_users()
    {
        $response = $this->get('/api/users', [
            'Authorization' => 'Bearer ' . $this->playerUser->api_token
        ]);

        $response->seeStatusCode(403);
    }

    public function test_unauthenticated_user_cannot_list_users()
    {
        $response = $this->get('/api/users');

        $response->seeStatusCode(401);
    }
    
    public function test_admin_can_show_specific_user()
    {
        $response = $this->get('/api/users/' . $this->playerUser->id, [
            'Authorization' => 'Bearer ' . $this->adminUser->api_token
        ]);

        $response->seeStatusCode(200);
        $response->seeJson([
            'email' => 'player@test.com'
        ]);
    }

    public function test_admin_cannot_show_nonexistent_user()
    {
        $response = $this->get('/api/users/99999', [
            'Authorization' => 'Bearer ' . $this->adminUser->api_token
        ]);

        $response->seeStatusCode(404);
    }

    public function test_player_cannot_show_user()
    {
        $response = $this->get('/api/users/' . $this->adminUser->id, [
            'Authorization' => 'Bearer ' . $this->playerUser->api_token
        ]);

        $response->seeStatusCode(403);
    }
    
    public function test_admin_can_create_new_user()
    {
        $userData = [
            'name' => 'New User',
            'email' => 'newuser@test.com',
            'password' => 'password123',
            'role_id' => $this->playerRole->id
        ];

        $response = $this->post('/api/users', $userData, [
            'Authorization' => 'Bearer ' . $this->adminUser->api_token
        ]);

        $response->seeStatusCode(201);
        
        $this->seeInDatabase('users', [
            'email' => 'newuser@test.com',
            'name' => 'New User'
        ]);
    }

    public function test_admin_can_create_user_without_role_id()
    {
        $userData = [
            'name' => 'User Without Role',
            'email' => 'norole@test.com',
            'password' => 'password123'
        ];

        $response = $this->post('/api/users', $userData, [
            'Authorization' => 'Bearer ' . $this->adminUser->api_token
        ]);

        $response->seeStatusCode(201);
    }

    public function test_store_user_validates_required_fields()
    {
        $response = $this->post('/api/users', [], [
            'Authorization' => 'Bearer ' . $this->adminUser->api_token
        ]);

        $response->seeStatusCode(422);
    }

    public function test_store_user_validates_email_format()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'password' => 'password123'
        ];

        $response = $this->post('/api/users', $userData, [
            'Authorization' => 'Bearer ' . $this->adminUser->api_token
        ]);

        $response->seeStatusCode(422);
    }

    public function test_store_user_validates_unique_email()
    {
        $userData = [
            'name' => 'Duplicate Email',
            'email' => 'admin@test.com',
            'password' => 'password123'
        ];

        $response = $this->post('/api/users', $userData, [
            'Authorization' => 'Bearer ' . $this->adminUser->api_token
        ]);

        $response->seeStatusCode(422);
    }

    public function test_store_user_validates_password_min_length()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => 'short'
        ];

        $response = $this->post('/api/users', $userData, [
            'Authorization' => 'Bearer ' . $this->adminUser->api_token
        ]);

        $response->seeStatusCode(422);
    }

    public function test_store_user_validates_name_min_length()
    {
        $userData = [
            'name' => 'A',
            'email' => 'test@test.com',
            'password' => 'password123'
        ];

        $response = $this->post('/api/users', $userData, [
            'Authorization' => 'Bearer ' . $this->adminUser->api_token
        ]);

        $response->seeStatusCode(422);
    }

    public function test_store_user_validates_role_exists()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => 'password123',
            'role_id' => 99999
        ];

        $response = $this->post('/api/users', $userData, [
            'Authorization' => 'Bearer ' . $this->adminUser->api_token
        ]);

        $response->seeStatusCode(422);
    }

    public function test_player_cannot_create_user()
    {
        $userData = [
            'name' => 'New User',
            'email' => 'newuser@test.com',
            'password' => 'password123'
        ];

        $response = $this->post('/api/users', $userData, [
            'Authorization' => 'Bearer ' . $this->playerUser->api_token
        ]);

        $response->seeStatusCode(403);
    }

    public function test_admin_can_update_user()
    {
        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@test.com'
        ];

        $response = $this->put('/api/users/' . $this->playerUser->id, $updateData, [
            'Authorization' => 'Bearer ' . $this->adminUser->api_token
        ]);

        $response->seeStatusCode(200);
        $response->seeJson([
            'name' => 'Updated Name',
            'email' => 'updated@test.com'
        ]);

        $this->seeInDatabase('users', [
            'id' => $this->playerUser->id,
            'name' => 'Updated Name',
            'email' => 'updated@test.com'
        ]);
    }

    public function test_admin_can_update_user_password()
    {
        $updateData = [
            'password' => 'newpassword123'
        ];

        $response = $this->put('/api/users/' . $this->playerUser->id, $updateData, [
            'Authorization' => 'Bearer ' . $this->adminUser->api_token
        ]);

        $response->seeStatusCode(200);

        $user = User::find($this->playerUser->id);
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }

    public function test_admin_can_update_user_role()
    {
        $updateData = [
            'role_id' => $this->adminRole->id
        ];

        $response = $this->put('/api/users/' . $this->playerUser->id, $updateData, [
            'Authorization' => 'Bearer ' . $this->adminUser->api_token
        ]);

        $response->seeStatusCode(200);

        $this->seeInDatabase('users', [
            'id' => $this->playerUser->id,
            'role_id' => $this->adminRole->id
        ]);
    }

    public function test_admin_can_partial_update_user()
    {
        $updateData = [
            'name' => 'Only Name Updated'
        ];

        $response = $this->put('/api/users/' . $this->playerUser->id, $updateData, [
            'Authorization' => 'Bearer ' . $this->adminUser->api_token
        ]);

        $response->seeStatusCode(200);
        $response->seeJson([
            'name' => 'Only Name Updated'
        ]);
    }

    public function test_update_user_validates_unique_email()
    {
        $updateData = [
            'email' => 'admin@test.com' 
        ];

        $response = $this->put('/api/users/' . $this->playerUser->id, $updateData, [
            'Authorization' => 'Bearer ' . $this->adminUser->api_token
        ]);

        $response->seeStatusCode(422);
    }

    public function test_update_user_allows_same_email()
    {
        $updateData = [
            'name' => 'Updated Name',
            'email' => 'player@test.com'
        ];

        $response = $this->put('/api/users/' . $this->playerUser->id, $updateData, [
            'Authorization' => 'Bearer ' . $this->adminUser->api_token
        ]);

        $response->seeStatusCode(200);
    }

    public function test_update_user_validates_password_min_length()
    {
        $updateData = [
            'password' => 'short'
        ];

        $response = $this->put('/api/users/' . $this->playerUser->id, $updateData, [
            'Authorization' => 'Bearer ' . $this->adminUser->api_token
        ]);

        $response->seeStatusCode(422);
    }

    public function test_update_user_validates_role_exists()
    {
        $updateData = [
            'role_id' => 99999
        ];

        $response = $this->put('/api/users/' . $this->playerUser->id, $updateData, [
            'Authorization' => 'Bearer ' . $this->adminUser->api_token
        ]);

        $response->seeStatusCode(422);
    }

    public function test_admin_cannot_update_nonexistent_user()
    {
        $updateData = [
            'name' => 'Updated Name'
        ];

        $response = $this->put('/api/users/99999', $updateData, [
            'Authorization' => 'Bearer ' . $this->adminUser->api_token
        ]);

        $response->seeStatusCode(404);
    }

    public function test_player_cannot_update_user()
    {
        $updateData = [
            'name' => 'Hacked Name'
        ];

        $response = $this->put('/api/users/' . $this->adminUser->id, $updateData, [
            'Authorization' => 'Bearer ' . $this->playerUser->api_token
        ]);

        $response->seeStatusCode(403);
    }

    public function test_admin_can_delete_user()
    {
        $userToDelete = User::create([
            'name' => 'User To Delete',
            'email' => 'delete@test.com',
            'password' => Hash::make('password123'),
            'role_id' => $this->playerRole->id,
        ]);

        $response = $this->delete('/api/users/' . $userToDelete->id, [], [
            'Authorization' => 'Bearer ' . $this->adminUser->api_token
        ]);

        $response->seeStatusCode(200);

        $this->notSeeInDatabase('users', [
            'id' => $userToDelete->id
        ]);
    }

    public function test_admin_cannot_delete_nonexistent_user()
    {
        $response = $this->delete('/api/users/99999', [], [
            'Authorization' => 'Bearer ' . $this->adminUser->api_token
        ]);

        $response->seeStatusCode(404);
    }

    public function test_player_cannot_delete_user()
    {
        $response = $this->delete('/api/users/' . $this->adminUser->id, [], [
            'Authorization' => 'Bearer ' . $this->playerUser->api_token
        ]);

        $response->seeStatusCode(403);
    }

    public function test_unauthenticated_user_cannot_delete_user()
    {
        $response = $this->delete('/api/users/' . $this->playerUser->id);

        $response->seeStatusCode(401);
    }


    public function test_list_users_returns_multiple_users()
    {
        User::create([
            'name' => 'User 3',
            'email' => 'user3@test.com',
            'password' => Hash::make('password123'),
            'role_id' => $this->playerRole->id,
        ]);

        User::create([
            'name' => 'User 4',
            'email' => 'user4@test.com',
            'password' => Hash::make('password123'),
            'role_id' => $this->playerRole->id,
        ]);

        $response = $this->get('/api/users', [
            'Authorization' => 'Bearer ' . $this->adminUser->api_token
        ]);

        $response->seeStatusCode(200);
        $data = json_decode($response->response->getContent(), true);
        
        $this->assertGreaterThanOrEqual(4, count($data));
    }

    public function test_store_user_with_all_valid_fields()
    {
        $userData = [
            'name' => 'Complete User',
            'email' => 'complete@test.com',
            'password' => 'password123456',
            'role_id' => $this->playerRole->id
        ];

        $response = $this->post('/api/users', $userData, [
            'Authorization' => 'Bearer ' . $this->adminUser->api_token
        ]);

        $response->seeStatusCode(201);
        
        $this->seeInDatabase('users', [
            'name' => 'Complete User',
            'email' => 'complete@test.com',
            'role_id' => $this->playerRole->id
        ]);
    }
}