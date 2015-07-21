<?php

use Illuminate\Database\Seeder;

class DocumentsTableSeeder extends Seeder
{
    public function run()
    {
        $stubContent = [
            factory('MXAbierto\Participa\Models\DocContent')->make([
                'content' => file_get_contents(base_path('database/stubs/the_last_question.md')),
            ]),
            factory('MXAbierto\Participa\Models\DocContent')->make([
                'content' => file_get_contents(base_path('database/stubs/logistic_regression.md')),
            ]),
        ];

        factory('MXAbierto\Participa\Models\Doc', 2)
            ->create()
            ->each(function ($doc, $key) use ($stubContent) {
                $doc->contents()->save($stubContent[$key]);
            });

        $docs = factory('MXAbierto\Participa\Models\Doc', 30)
            ->create()
            ->each(function ($doc) {
                $doc->contents()->save(factory('MXAbierto\Participa\Models\DocContent')->make());
            });
    }
}
