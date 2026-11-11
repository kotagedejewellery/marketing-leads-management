<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class BootstrapAdmin extends Command
{
    protected $signature = 'kgj:bootstrap-admin {--name=} {--email=} {--password=}';

    protected $description = 'Membuat admin awal jika belum ada admin aktif.';

    public function handle(): int
    {
        if (DB::table('users')->where('role', 'admin')->where('active', true)->exists()) {
            $this->error('Admin aktif sudah tersedia. Gunakan menu Akun untuk mengelola akses.');

            return self::FAILURE;
        }

        $data = Validator::make([
            'name' => $this->option('name') ?: $this->ask('Nama admin'),
            'email' => $this->option('email') ?: $this->ask('Email admin'),
            'password' => $this->option('password') ?: $this->secret('Kata sandi admin'),
        ], [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:12'],
        ])->validate();

        DB::table('users')->insert([
            'name' => $data['name'],
            'email' => $data['email'],
            'password_hash' => Hash::make($data['password']),
            'role' => 'admin',
            'created_at' => now(),
        ]);

        $this->info('Admin awal berhasil dibuat.');

        return self::SUCCESS;
    }
}
