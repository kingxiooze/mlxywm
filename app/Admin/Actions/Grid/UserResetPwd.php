<?php

namespace App\Admin\Actions\Grid;

use App\Models\User;
use Dcat\Admin\Actions\Response;
use Dcat\Admin\Grid\RowAction;
use Dcat\Admin\Traits\HasPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserResetPwd extends RowAction
{
    /**
     * @return string
     */
	protected $title = '重置密码';

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
        $user = User::find($user_id);
        if (empty($user)) {
            $this->response()->error("用户不存在");
        }
        $user->password = Hash::make("123456");
        $user->save();
        return $this->response()
            ->success('密码已经重置为123456')
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
