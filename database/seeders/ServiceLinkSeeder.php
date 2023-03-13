<?php

namespace Database\Seeders;

use App\Models\ServiceLink;
use Illuminate\Database\Seeder;

class ServiceLinkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $list = array(
            'https://otomoni.beer/',
            'https://always.fan/lp/lunch/',
            'https://goace.jp/lp/teigaku/',
            'https://mechakari.com/',
            'https://goopass.jp/',
            'https://kurand.jp/pages/kurand-club',
            'https://club.ehonnavi.net/',
            'https://toysub.net/',
            'https://www.favy.jp/plans/735',
            'https://www.favy.jp/plans/803',
            'https://www.keihanhotels-resorts.co.jp/',
            'https://www.kanondo.coffee/',
            'https://backpackersjapancafe.stores.jp/items/5c55786a3b636535fa7fda6f',
            'https://teerex.golf/',
            'https://subsclife.com/',
            'https://clas.style/',
            'https://sa-nu.com/?lng=ja',
            'https://laxus.co/',
            'https://bloomeelife.com/',
            'https://nativecamp.net/',
            'https://home.craftie.jp/',
            'https://snaq.me/',
            'https://postcoffee.co/',
            'https://hitohana.tokyo/',
            'https://basefood.co.jp/',
            'https://www.inic-market.com/cafemaison/',
            'https://subsc.jp/',
            'https://www.cosme.net/bloombox/',
            'https://mechakari.com/',
            'https://biz-fuku.com/shopping/lp.php?p=top',
            'https://www.lenet.jp/',
            'https://goopass.jp/',
            'https://www.zerocafe.info/',
            'https://officepass.nikkei.com/user/top.php',
            'https://hideoutclub.jp/',
            'https://kurand.jp/',
            'https://www.the-stella.com/',
            'https://club.ehonnavi.net/',
            'https://www.worldlibrary.co.jp/',
            'https://toysub.net/',
            'https://carmo-kun.jp/',
            'https://doctors-me.com/',
            'https://sasaya-company.jp/',
            'https://address.love/',
            'https://hostellife.jp/',
            'https://x-house.co.jp/',
            'https://www.hafh.com/',
            'https://www.favy.info/lucuafoodpassport_lp',
            'https://elmersgreen.com/',
            'https://meeth.store/',
        );

        foreach ($list as $url) {
            $link = new ServiceLink();
            $link['url'] = $url;
            $link['jump_count'] = 0;
            $link->save();
        }
    }
}
