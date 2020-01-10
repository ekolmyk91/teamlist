<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class FillDefaultUsersAndRoles extends Migration
{
    /**
     * @var array
     */
    protected $roles;
    /**
     * @var array
     */
    protected $users;

    /**
     * @var string
     */

    private $userTable = 'users';
    /**
     * @var string
     */
    private $password = 'JyBpigmtnB';


    public function __construct()
    {


        //Add new roles
        $this->roles = [
          [
            'name' => 'admin',
          ],
          [
            'name' => 'member',
          ],
        ];

        //Add new users
        $this->users = [
          [
            'name' => 'Administrator',
            'email' => 'admin@example.test',
            'email_verified_at' => null,
            'password' => Hash::make($this->password),
            'remember_token' => null,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'roles' => [
              [
                'name' => 'admin',
              ],
            ],
          ],
        ];
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //Fill Roles
        foreach ($this->roles as $role) {
            $roleItem = DB::table('roles')->where([
              'name' => $role['name'],
            ])->first();

            if ($roleItem === null) {
                $roleId = DB::table('roles')->insertGetId($role);
            } else {
                $roleId = $roleItem->id;
            }
        }

        // Fill Users.
        foreach ($this->users as $user) {
            $roles = $user['roles'];
            unset($user['roles']);

            $userItem = DB::table($this->userTable)->where([
              'email' => $user['email'],
            ])->first();

            if ($userItem === null) {
                $userId = DB::table($this->userTable)->insertGetId($user);

                foreach ($roles as $role) {
                    $roleItem = DB::table('roles')->where([
                      'name' => $role['name'],
                    ])->first();

                    $roleUserData = [
                      'user_id' => $userId,
                      'role_id' => $roleItem->id,
                    ];
                    $modelHasRoleItem = DB::table('role_user')->where($roleUserData)->first();
                    if ($modelHasRoleItem === null) {
                        DB::table('role_user')->insert($roleUserData);
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
