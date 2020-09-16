<?php
/**
 * 發放用戶獎勵 事件
 * @author Weine
 * @date 2020-9-16
 */
namespace App\Listeners\Roulette;

use App\Events\RouletteReward;
use App\Facades\UserSer;
use App\Repositories\UserItemRepository;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class AddUserReward
{
    // 加鑽石獎勵類型
    const ADD_POINT_TYPE = 1;

    // 加經驗獎勵類型
    const ADD_EXP_TYPE = 2;

    protected $userItemRepository;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(UserItemRepository $userItemRepository)
    {
        $this->userItemRepository = $userItemRepository;
    }

    /**
     * Handle the event.
     *
     * @param  RouletteReward  $event
     * @return void
     */
    public function handle(RouletteReward $event)
    {
        try {
            // 獎項依類型歸類
            $reward = collect($event->reward)->mapToGroups(function ($item, $key) {
                return [$item['type'] => $item['amount']];
            });

            // 發放獎勵
            foreach ($reward as $type => $v) {
                if ($type === self::ADD_POINT_TYPE) {
                    //加鑽
                    UserSer::updateUserInfo($event->user->uid, ['points' => $event->user->points + $v->sum()]);
                } elseif ($type === self::ADD_EXP_TYPE) {
                    //加經驗
                    UserSer::updateUserInfo($event->user->uid, $data = ['exp' => $event->user->exp + $v->sum()]);
                } else {
                    // 加物品
                    $addItems = [];
                    foreach ($v as $itemId => $item) {
                        $addItems[] = [
                            'item_id' => $type,
                            'uid'     => $event->user->uid,
                            'status'  => 0,
                        ];
                    }

                    $this->userItemRepository->insertGift($addItems);
                }
            }
        } catch (\Exception $e) {
            report($e);
            Log::error('發放中獎用戶輪盤獎勵錯誤!');
        }
    }
}
