<?php

namespace Database\Seeders;

use App\Admin\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createAdminAccount();
    }

    /**
     * @return void
     */
    protected function createAdminAccount()
    {
        /** @var Admin $admin */
        $admin = Admin::query()->firstOrCreate([
            'username' => 'admin',
        ], [
            'password' => Hash::make('123456'),
            'status' => 1,
        ]);
        if (empty($admin->password)) {
            $admin->forceFill([
                'password' => Hash::make('123456'),
            ])->save();
        }
    }
}
