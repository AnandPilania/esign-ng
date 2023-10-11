<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(AdminsTableSeeder::class);
        $this->call(AgencyTableSeeder::class);
        $this->call(CompanyTableSeeder::class);
        $this->call(CompanyConsigneeTableSeeder::class);
        $this->call(CompanyRemoteSignTableSeeder::class);
        $this->call(CompanySignatureTableSeeder::class);
        $this->call(UserTableSeeder::class);
        $this->call(PositionsTableSeeder::class);
        $this->call(DepartmentsTableSeeder::class);
        $this->call(RoleTableSeeder::class);
        $this->call(PermissionTableSeeder::class);
        $this->call(RolePermissionTableSeeder::class);
        $this->call(ConfigParamsTableSeeder::class);
        $this->call(DocumentTypeSeeder::class);
        $this->call(UtilitiesDocumentTypeSeeder::class);
        $this->call(ConversationTemplateSeeder::class);
        $this->call(AmsRolesTableSeeder::class);
        $this->call(VendorsTableSeeder::class);
        $this->call(UpdateAddendumSeeder::class);
        $this->call(CompanyConfigSeeder::class);
//        $this->call(DocumentAssigneeTableSeeder::class);
    }
}
