<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BanksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('banks')->delete();
        $banks = ['三菱 ＵＦＪ', 'みずほ銀行', 'りそな銀行', '埼玉りそな銀行', '三井住友銀行', 'ＰａｙＰａｙ銀行', '楽天銀行', 'ゆうちょ銀行'];
        foreach ($banks as $key => $bank){
            DB::table('banks')->insert([
                'bank_name' => $bank,
            ]);
        }
    }
}
