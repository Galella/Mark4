<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Office;
use App\Models\Outlet;
use App\Models\OutletType;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        // Buat outlet types
        $cabangType = OutletType::create([
            'name' => 'Cabang',
            'description' => 'Outlet tipe cabang'
        ]);
        
        $agenType = OutletType::create([
            'name' => 'Agen',
            'description' => 'Outlet tipe agen'
        ]);
        
        $geraiType = OutletType::create([
            'name' => 'Gerai',
            'description' => 'Outlet tipe gerai'
        ]);

        // Buat struktur organisasi
        $officePusat = Office::create([
            'name' => 'Kantor Pusat',
            'code' => 'KP-001',
            'type' => 'pusat',
            'description' => 'Kantor Pusat Organisasi',
            'is_active' => true
        ]);
        
        $officeWilayah = Office::create([
            'name' => 'Kantor Wilayah Jakarta',
            'code' => 'KW-001',
            'type' => 'wilayah',
            'parent_id' => $officePusat->id,
            'description' => 'Kantor Wilayah Jakarta',
            'is_active' => true
        ]);
        
        $officeArea = Office::create([
            'name' => 'Kantor Area Jakarta Pusat',
            'code' => 'KA-001',
            'type' => 'area',
            'parent_id' => $officeWilayah->id,
            'description' => 'Kantor Area Jakarta Pusat',
            'is_active' => true
        ]);

        // Buat outlet
        $outlet = Outlet::create([
            'name' => 'Outlet A',
            'code' => 'OA-001',
            'office_id' => $officeArea->id,
            'outlet_type_id' => $cabangType->id,
            'description' => 'Sample outlet',
            'is_active' => true
        ]);

        // Buat Super Admin
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('password'),
            'role' => 'super_admin'
        ]);

        // Buat Admin Wilayah
        User::create([
            'name' => 'Admin Wilayah Jakarta',
            'email' => 'adminwilayah@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin_wilayah',
            'office_id' => $officeWilayah->id
        ]);

        // Buat Admin Area
        User::create([
            'name' => 'Admin Area Jakarta Pusat',
            'email' => 'adminarea@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin_area',
            'office_id' => $officeArea->id
        ]);

        // Buat Admin Outlet
        User::create([
            'name' => 'Admin Outlet A',
            'email' => 'adminoutlet@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin_outlet',
            'outlet_id' => $outlet->id
        ]);
    }
}