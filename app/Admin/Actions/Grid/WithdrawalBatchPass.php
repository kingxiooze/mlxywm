<?php

namespace App\Admin\Actions\Grid;

use App\Models\UserWithdrawal;
use Dcat\Admin\Actions\Response;
use Dcat\Admin\Grid\BatchAction;
use Dcat\Admin\Traits\HasPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class WithdrawalBatchPass extends BatchAction
{
    /**
     * @return string
     */
	protected $title = '批量审核通过';

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
        UserWithdrawal::whereIn("id", $ids)->update([
            "status" => 1,
            "is_manager"=>1,
            "pay_status"=>1,
            "order_status"=>1
        ]);
        return $this->response()
            ->success('操作成功')
            ->redirect('/user_withdrawal');
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
