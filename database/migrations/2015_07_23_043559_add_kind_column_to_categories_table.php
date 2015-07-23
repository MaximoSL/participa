<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddKindColumnToCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('kind')->default('category')->after('name');
            $table->dropUnique('categories_name_unique');
            $table->unique(['name', 'kind']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique('categories_name_kind_unique');
            $table->dropColumn('kind');
            $table->unique('name');
        });
    }
}
