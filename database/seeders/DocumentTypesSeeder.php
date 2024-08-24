<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DocumentTypesSeeder extends Seeder
{
    public function run()
    {
        DB::table('document_types')->insert([
            ['name' => 'CC'],
            ['name' => 'TI'],
            ['name' => 'RC'],
            ['name' => 'CE'],
            ['name' => 'PEP'],
            ['name' => 'NIT'],
        ]);
    }
}
