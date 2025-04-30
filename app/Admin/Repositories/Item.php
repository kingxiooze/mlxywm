<?php

namespace App\Admin\Repositories;

use App\Models\Item as Model;
use Dcat\Admin\Repositories\EloquentRepository;
use Dcat\Admin\Form;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Cache\LockTimeoutException;

class Item extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;

    /**
     * 更新数据.
     *
     * @param  Form  $form
     * @return bool
     */
    public function update(Form $form)
    {
        $result = false;

        $id = $form->getKey();
        // 尝试获取锁
        $lock = Cache::lock('ITEM_JOINED_COUNT_LOCK:' . $id, 10);
        try {
            // 如果未获取到锁则尝试重新获取，每秒1次
            $lock->block(5);
            $result = parent::update($form);
        } catch (LockTimeoutException $e) {
            return $form->response()->error('无法获取到修改锁，请重试');
        } finally {
            $lock?->release();
        }

        return $result;
    }
}
