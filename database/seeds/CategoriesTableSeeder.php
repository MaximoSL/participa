<?php

use Illuminate\Database\Seeder;

class CategoriesTableSeeder extends Seeder
{
    public function run()
    {
        // Uncomment the below to wipe the table clean before populating
        // DB::table('categories')->truncate();

        $categories = [
            [
                'name'       => 'Category 1',
                'kind'       => 'category',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ],
            [
                'name'       => 'Category 2',
                'kind'       => 'category',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ],
            [
                'name'       => 'Cofemer',
                'kind'       => 'layout',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ],
            [
                'name'       => 'Votos',
                'kind'       => 'layout',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ],
            [
                'name'       => 'Institution 1',
                'kind'       => 'institution',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ],
            [
                'name'       => 'Institution 2',
                'kind'       => 'institution',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ],
        ];

        // Uncomment the below to run the seeder
        DB::table('categories')->insert($categories);
    }
}
