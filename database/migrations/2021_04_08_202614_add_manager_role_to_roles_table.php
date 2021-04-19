<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AddManagerRoleToRolesTable extends Migration
{
    /**
     * @var array
     */
    protected $roles;

    public function __construct()
    {
        //Add new roles
        $this->roles = [
            [
                'name' => 'manager',
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
