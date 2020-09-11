<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('params', function (Blueprint $table) {
            $table->id();
            $table->integer('api_id')->comment('接口参数');
            $table->tinyInteger('type')->default(1)->comment('1.url参数 2.query参数 3.body参数');
            $table->string('name')->nullable()->comment('参数名称');
            $table->string('is_must')->nullable()->comment('是否必填');
            $table->string('desc')->nullable()->comment('描述');
            $table->string('example')->nullable()->comment('事例');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('params');
    }
}
