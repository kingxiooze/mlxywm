<?php

namespace App\Admin\Actions\Grid;

use App\Models\UserBankcard;
use Dcat\Admin\Actions\Response;
use Dcat\Admin\Grid\RowAction;
use Dcat\Admin\Traits\HasPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class UserResetBankcard extends RowAction
{
    /**
     * @return string
     */
	protected $title = '重置银行卡';

    /**
     * Handle the action request.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function handle(Request $request)
    {
        $user_id = $this->getKey();
        UserBankcard::where("user_id", $user_id)->delete();
        return $this->response()
            ->success('银行卡已清除')
            ->redirect('/user');
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
