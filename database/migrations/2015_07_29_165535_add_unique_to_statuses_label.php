<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddUniqueToStatusesLabel extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('statuses', function (Blueprint $table) {
            $table->unique('label');
        });

        DB::statement("
                INSERT INTO statuses
                  (label, created_at, updated_at)
                VALUES
                  ('Documento Abierto', NOW(), NOW()),
                  ('Documento cerrado a comentarios', NOW(), NOW()),
                  ('Documento cerrado', NOW(), NOW())
                ON DUPLICATE KEY UPDATE
                  updated_at     = VALUES(updated_at)
            ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('statuses', function (Blueprint $table) {
            $table->dropUnique('statuses_label_unique');
        });
    }
}
