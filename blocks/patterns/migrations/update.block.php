<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class {:class:} extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::{:connection:}table('{:table:}', function (Blueprint $table) {
{:fields:}{:engine:}{:indexes:}{:collation:}{:charset:}
        });
{:statements:}
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::{:connection:}table('{:table:}', function (Blueprint $table) {
{:dropFields:}
        });
    }
}