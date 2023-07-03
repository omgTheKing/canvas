<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddParentIdToTopics extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('blog_topics', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->constrained('blog_topics')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('blog_topics', function (Blueprint $table) {
            $table->dropColumn('parent_id');
        });
    }
}
