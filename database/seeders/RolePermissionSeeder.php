<?php

namespace Database\Seeders;

use App\Models\Buku;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = ['lihat buku', 'tambah buku', 'edit buku', 'hapus buku', 'kelola role pengguna'];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        Role::findOrCreate('Admin', 'web')->syncPermissions($permissions);
        Role::findOrCreate('Dosen', 'web')->syncPermissions(['lihat buku', 'tambah buku', 'edit buku']);
        Role::findOrCreate('Mahasiswa', 'web')->syncPermissions(['lihat buku']);

        foreach ([
            ['name' => 'Admin Utama', 'email' => 'admin@buku.com', 'role' => 'Admin'],
            ['name' => 'Dosen Pengajar', 'email' => 'dosen@buku.com', 'role' => 'Dosen'],
            ['name' => 'Mahasiswa Aktif', 'email' => 'mahasiswa@buku.com', 'role' => 'Mahasiswa'],
        ] as $account) {
            $user = User::updateOrCreate(
                ['email' => $account['email']],
                ['name' => $account['name'], 'password' => Hash::make(config('app.demo_user_password'))]
            );
            $user->syncRoles([$account['role']]);
        }

        foreach ([
            ['Senja di Ujung Kota', 'Nadia Prameswari', 'Pustaka Aksara', 2024],
            ['Arsitektur Pikiran', 'Raka Mahendra', 'Ruang Kata', 2023],
            ['Langit yang Kita Bagi', 'Dewi Anggraini', 'Kertas Utama', 2025],
            ['Catatan dari Perpustakaan', 'Bima Adinata', 'Penerbit Selatan', 2022],
        ] as [$judul, $penulis, $penerbit, $tahun]) {
            Buku::firstOrCreate(
                ['judul' => $judul],
                ['penulis' => $penulis, 'penerbit' => $penerbit, 'tahun_terbit' => $tahun]
            );
        }
    }
}
