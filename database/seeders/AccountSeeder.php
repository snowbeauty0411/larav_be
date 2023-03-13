<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {   

        DB::table('accounts')->insert(
            [
                'email' => 'seller@gmail.com',
                'password' => Hash::make('12345678'),
                'classification_id' => 1,
                'business_type' => 1
            ]
        );

        DB::table('accounts')->insert(
            [
                'email' => 'buyer@gmail.com',
                'password' => Hash::make('12345678'),
                'classification_id' => 1,
                'business_type' => 1
            ]
        );

        DB::table('accounts')->insert(
            [
                'email' => 'seller2@gmail.com',
                'password' => Hash::make('12345678'),
                'classification_id' => 1,
                'business_type' => 2

            ]
        );

        for ($i = 1; $i <= 50; $i++) {
            DB::table('accounts')->insert(
                [
                    'email' => 'buyer' . $i . '@gmail.com',
                    'password' => Hash::make('12345678'),
                    'classification_id' => 1
                ]
            );
        }

    }
}
