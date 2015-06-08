<?php

namespace MXAbierto\Participa\Migrations;

use Illuminate\Support\Facades\Db;
use Illuminate\Database\Migrations\Migration;

abstract class DualMigration extends Migration implements DualMigrationInterface
{
  public function up()
  {
      $connection = Db::connection()->getDriverName();

      switch ($connection) {
      case 'mysql':
        $this->upMySQL();
        break;

      case 'sqlite':
        $this->upSQLite();
        break;

      default:
        throw new Exception("Unknown connection $connection");
    }
  }

    public function down()
    {
        $connection = Db::connection()->getDriverName();

        switch ($connection) {
      case 'mysql':
        $this->downMySQL();
        break;

      case 'sqlite':
        $this->downSQLite();
        break;

      default:
        throw new Exception("Unknown connection $connection");
    }
    }

    abstract public function upMySQL();
    abstract public function downMySQL();
    abstract public function upSQLite();
    abstract public function downSQLite();
}
