<?php

use Illuminate\Database\Seeder;

class StatusesTableSeeder extends Seeder
{
    public function run()
    {
        // Uncomment the below to wipe the table clean before populating
        // DB::table('statuses')->truncate();

        $statuses = [
            [
                'label'      => 'Documento Abierto',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ],
            [
                'label'      => 'Documento cerrado a comentarios',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ],
            [
                'label'      => 'Documento cerrado',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ],
        ];

        // Uncomment the below to run the seeder
        DB::table('statuses')->insert($statuses);
    }
}
