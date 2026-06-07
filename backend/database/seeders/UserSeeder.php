<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::firstOrCreate(['email' => 'admin@laptopshop.com'], [
            'name'      => 'Admin LaptopShop',
            'password'  => Hash::make('Admin@123'),
            'role'      => 'admin',
            'phone'     => '0901234567',
            'address'   => '123 Nguyễn Huệ, Quận 1, TP.HCM',
            'is_active' => true,
        ]);

        // Customers mẫu
        $users = [
            ['name' => 'Nguyễn Văn An',   'email' => 'an.nguyen@gmail.com',   'phone' => '0912345678', 'address' => '45 Lê Lợi, Quận 1, TP.HCM'],
            ['name' => 'Trần Thị Bình',   'email' => 'binh.tran@gmail.com',   'phone' => '0923456789', 'address' => '78 Hai Bà Trưng, Quận 3, TP.HCM'],
            ['name' => 'Lê Minh Khoa',    'email' => 'khoa.le@gmail.com',     'phone' => '0934567890', 'address' => '12 Đinh Tiên Hoàng, Quận Bình Thạnh, TP.HCM'],
            ['name' => 'Phạm Thị Lan',    'email' => 'lan.pham@gmail.com',    'phone' => '0945678901', 'address' => '56 Võ Văn Tần, Quận 3, TP.HCM'],
            ['name' => 'Hoàng Đức Mạnh',  'email' => 'manh.hoang@gmail.com',  'phone' => '0956789012', 'address' => '89 Cách Mạng Tháng 8, Quận 10, TP.HCM'],
            ['name' => 'Vũ Thị Ngọc',     'email' => 'ngoc.vu@gmail.com',     'phone' => '0967890123', 'address' => '34 Phan Xích Long, Quận Phú Nhuận, TP.HCM'],
            ['name' => 'Đặng Văn Phúc',   'email' => 'phuc.dang@gmail.com',   'phone' => '0978901234', 'address' => '67 Nguyễn Thị Minh Khai, Quận 1, TP.HCM'],
        ];

        foreach ($users as $u) {
            User::firstOrCreate(['email' => $u['email']], array_merge($u, [
                'password'  => Hash::make('Password@123'),
                'role'      => 'user',
                'is_active' => true,
            ]));
        }
    }
}
