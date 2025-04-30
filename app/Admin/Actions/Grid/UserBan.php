<?php

namespace App\Admin\Actions\Grid;

use Dcat\Admin\Actions\Response;
use Dcat\Admin\Grid\RowAction;
use Dcat\Admin\Traits\HasPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Models\User;

class UserBan extends RowAction
{
    /**
     * @return string
     */
	// protected $title = '封禁';

    public function title(){
        if(empty($this->row->baned_at)) {
            return "封禁";
        } else {
            return "解封";
        }
    }

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
        if (empty($user->baned_at)) {
            $user->baned_at = now();
        } else {
            $user->baned_at = null;
        }
        $user->save();
        return $this->response()
            ->success('操作完成')
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
