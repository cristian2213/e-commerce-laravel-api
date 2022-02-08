<?php

use App\Utils\RolesUtils;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->enum('name', RolesUtils::getRolesAllowed());
            $table->timestamps();
            $table->softDeletes();
            // ********* 1:N **********
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            // ************************
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('roles');
    }
}
