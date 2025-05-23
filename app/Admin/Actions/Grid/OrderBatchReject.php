<?php

namespace App\Admin\Actions\Grid;

use App\Models\Order;
use Dcat\Admin\Actions\Response;
use Dcat\Admin\Grid\BatchAction;
use Dcat\Admin\Traits\HasPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class OrderBatchReject extends BatchAction
{
    /**
     * @return string
     */
	protected $title = '手动到账拒绝';

    /**
     * Handle the action request.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function handle(Request $request)
    {
        $ids = $this->getKey();
        Order::whereIn("id", $ids)->update([
            "order_status" => 3
        ]);
        return $this->response()
            ->success('操作成功')
            ->redirect('/order');
    }

    /**
	 * @return string|array|void
	 */
	public function confirm()
	{
		// return ['Confirm?', 'contents'];
	}

    /**
     * @param Model|Authenticatable|HasPermissions|null $user
     *
     * @return bool
     */
    protected function authorize($user): bool
    {
        return true;
    }

    /**
     * @return array
     */
    protected function parameters()
    {
        return [];
    }
}
