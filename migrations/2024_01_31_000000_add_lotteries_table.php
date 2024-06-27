<?php



use Flarum\Database\Migration;
use Illuminate\Database\Schema\Blueprint;

return Migration::createTable('lotteries1', function (Blueprint $table) {
    $table->increments('id');

    $table->string('question');

    $table->integer('discussion_id')->unsigned();
    $table->integer('user_id')->unsigned()->nullable();
    $table->integer('vote_count')->unsigned();
    $table->integer('max_votes')->unsigned();

    $table->boolean('allow_multiple_votes');


    $table->boolean('public_lottery');
    $table->json('settings');
    $table->timestamp('end_date')->nullable();
    $table->timestamps();

    $table->foreign('discussion_id')->references('id')->on('discussions')->onDelete('cascade');
    $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
});
