<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Department;
use App\Models\Branch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $branch = Branch::first();
        $depts = Department::pluck('id', 'name');

        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'admin@nexaerp.com',
                'department' => 'IT',
                'role' => 'Super Admin',
            ],
            [
                'name' => 'Ahmad Fauzi',
                'email' => 'ahmad.fauzi@nexaerp.com',
                'department' => 'Sales',
                'role' => 'Sales Manager',
            ],
            [
                'name' => 'Siti Rahayu',
                'email' => 'siti.rahayu@nexaerp.com',
                'department' => 'Purchasing',
                'role' => 'Purchase Manager',
            ],
            [
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@nexaerp.com',
                'department' => 'Finance',
                'role' => 'Accountant',
            ],
            [
                'name' => 'Dewi Lestari',
                'email' => 'dewi.lestari@nexaerp.com',
                'department' => 'HR',
                'role' => 'HR Manager',
            ],
            [
                'name' => 'Rudi Pratama',
                'email' => 'rudi.pratama@nexaerp.com',
                'department' => 'Operations',
                'role' => 'Warehouse Staff',
            ],
            [
                'name' => 'Eko Wijaya',
                'email' => 'eko.wijaya@nexaerp.com',
                'department' => 'IT',
                'role' => 'Admin',
            ],
            [
                'name' => 'Maya Anggraeni',
                'email' => 'maya.anggraeni@nexaerp.com',
                'department' => 'IT',
                'role' => 'Project Manager',
            ],
            [
                'name' => 'Viewer User',
                'email' => 'viewer@nexaerp.com',
                'department' => 'IT',
                'role' => 'Viewer',
            ],
        ];

        foreach ($users as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('password'),
                    'branch_id' => $branch?->id,
                    'department_id' => $depts[$userData['department']] ?? null,
                    'status' => 'active',
                ]
            );
            $user->syncRoles([$userData['role']]);
        }
    }
}
