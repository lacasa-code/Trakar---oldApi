<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MetaTags;

class MetaTagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        MetaTags::truncate();
        MetaTags::create([
        	'title'       => 'login',
        	'description' => 'trkar',
        	'keywords'    => 'trkar, trkar login, trkar app',
        ]);
    }
}
