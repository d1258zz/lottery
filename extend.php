<?php

/*
 * This file is part of d1258zz/lottery.
 *
 * Copyright (c) d1258zz.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace D1258zz\Lottery;

use Flarum\Api\Controller;
use Flarum\Api\Serializer\DiscussionSerializer;
use Flarum\Api\Serializer\ForumSerializer;
use Flarum\Api\Serializer\PostSerializer;
use Flarum\Discussion\Discussion;
use Flarum\Extend;
use Flarum\Post\Event\Saving as PostSaving;
use Flarum\Post\Post;
use Flarum\Settings\Event\Saved as SettingsSaved;
use D1258zz\Lottery\Api\Controllers;
use D1258zz\Lottery\Api\Serializers\LotterySerializer;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js')
        ->css(__DIR__.'/resources/less/forum.less'),

    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js')
        ->css(__DIR__.'/resources/less/admin.less'),

    new Extend\Locales(__DIR__.'/resources/locale'),

    (new Extend\Routes('api'))
        ->post('/d1258zz/lottery', 'd1258zz.lottery.create', Controllers\CreateLotteryController::class)
        ->get('/d1258zz/lottery/{id}', 'd1258zz.lottery.show', Controllers\ShowLotteryController::class)
        ->patch('/d1258zz/lottery/{id}', 'd1258zz.lottery.edit', Controllers\EditLotteryController::class)
        ->delete('/d1258zz/lottery/{id}', 'd1258zz.lottery.delete', Controllers\DeleteLotteryController::class)
        ->patch('/d1258zz/lottery/{id}/enter', 'd1258zz.lottery.enter', Controllers\EnterLotteryController::class),

    (new Extend\Model(Post::class))
        ->hasMany('lottery', Lottery::class, 'post_id', 'id'),

    (new Extend\Model(Discussion::class))
        ->hasMany('lottery', Lottery::class, 'post_id', 'first_post_id'),

    (new Extend\Event())
        ->listen(PostSaving::class, Listeners\SaveLotteryToDatabase::class)
        ->listen(SettingsSaved::class, Listeners\ClearFormatterCache::class),

    (new Extend\ApiSerializer(DiscussionSerializer::class))
        ->attributes(Api\AddDiscussionAttributes::class),

    (new Extend\ApiSerializer(PostSerializer::class))
        ->hasMany('lottery', LotterySerializer::class)
        ->attributes(Api\AddPostAttributes::class),

    (new Extend\ApiSerializer(ForumSerializer::class))
        ->attributes(Api\AddForumAttributes::class),

    (new Extend\ApiController(Controller\ListDiscussionsController::class))
        ->addOptionalInclude(['firstPost.lottery']),

    (new Extend\ApiController(Controller\ShowDiscussionController::class))
        ->addInclude(['posts.lottery', 'posts.lottery.options', 'posts.lottery.lottery_participants', 'posts.lottery.lottery_participants.option'])
        ->addOptionalInclude(['posts.lottery.participants', 'posts.lottery.participants.user']),

    (new Extend\ApiController(Controller\CreateDiscussionController::class))
        ->addInclude(['firstPost.lottery', 'firstPost.lottery.options', 'firstPost.lottery.lottery_participants', 'firstPost.lottery.lottery_participants.option'])
        ->addOptionalInclude(['firstPost.lottery.participants', 'firstPost.lottery.participants.user']),

    (new Extend\ApiController(Controller\CreatePostController::class))
        ->addInclude(['lottery', 'lottery.options', 'lottery.participants', 'lottery.participants.user'])
        ->addOptionalInclude(['lottery.participants', 'lottery.participants.user']),

    (new Extend\ApiController(Controller\ListPostsController::class))
        ->addInclude(['lottery', 'lottery.options', 'lottery.participants', 'lottery.participants.user', 'lottery.lottery_participants'])
        ->addOptionalInclude(['lottery.participants', 'lottery.participants.user']),

    (new Extend\ApiController(Controller\ShowPostController::class))
        ->addInclude(['lottery', 'lottery.options', 'lottery.participants', 'lottery.participants.user', 'lottery.lottery_participants'])
        ->addOptionalInclude(['lottery.participants', 'lottery.participants.user']),


    (new Extend\Console())
        ->command(Console\RefreshParticipantsCountCommand::class)
        ->command(Console\DrawCommand::class)
        ->schedule(Console\DrawCommand::class,Console\DrawSchedule::class),

    (new Extend\Policy())
        ->modelPolicy(Lottery::class, Access\LotteryPolicy::class)
        ->modelPolicy(Post::class, Access\PostPolicy::class),

    (new Extend\Settings())
        ->default('d1258zz-lottery.maxOptions', 10)
        ->default('d1258zz-lottery.optionsColorBlend', true)
        ->serializeToForum('allowLotteryOptionImage', 'd1258zz-lottery.allowOptionImage', 'boolval')
        ->serializeToForum('lotteryMaxOptions', 'd1258zz-lottery.maxOptions', 'intval')
        ->registerLessConfigVar('d1258zz-lottery-options-color-blend', 'd1258zz-lottery.optionsColorBlend', function ($value) {
            return $value ? 'true' : 'false';
        }),

    (new Extend\ModelVisibility(Lottery::class))
        ->scope(Access\ScopeLotteryVisibility::class),
];
