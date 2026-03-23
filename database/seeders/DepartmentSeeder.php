<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            [
                'name'        => 'Management',
                'description' => 'Company management and executive team',
                'is_active'   => true,
            ],
            [
                'name'        => 'Engineering',
                'description' => 'Software development and engineering team',
                'is_active'   => true,
            ],
            [
                'name'        => 'HR',
                'description' => 'Human resources and people operations',
                'is_active'   => true,
            ],
            [
                'name'        => 'QA',
                'description' => 'Quality assurance and testing team',
                'is_active'   => true,
            ],
            [
                'name'        => 'DevOps',
                'description' => 'Infrastructure and deployment operations',
                'is_active'   => true,
            ],
        ];

        foreach ($departments as $department) {
            Department::firstOrCreate(
                ['name' => $department['name']],
                $department
            );
        }
    }
}