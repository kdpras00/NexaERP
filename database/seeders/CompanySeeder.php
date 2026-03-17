<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::create([
            'name' => 'NexaERP Global Inc.',
            'address' => '123 Business Avenue, Suite 100',
            'phone' => '+1 800 123 4567',
            'email' => 'contact@nexaerp.com',
            'tax_number' => 'TAX-123456789'
        ]);

        $hqBranch = Branch::create([
            'company_id' => $company->id,
            'name' => 'Headquarters',
            'address' => '123 Business Avenue, Suite 100',
            'phone' => '+1 800 123 4567',
            'manager' => 'John Doe'
        ]);

        $departments = ['Sales', 'Purchasing', 'Finance', 'HR', 'IT', 'Operations'];
        foreach ($departments as $dept) {
            Department::create([
                'branch_id' => $hqBranch->id,
                'name' => $dept,
                'manager' => 'Manager of ' . $dept
            ]);
        }
    }
}
