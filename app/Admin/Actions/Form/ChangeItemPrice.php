<?php

namespace App\Admin\Actions\Form;

use Dcat\Admin\Actions\Response;
use Dcat\Admin\Form\AbstractTool;
use Dcat\Admin\Traits\HasPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Admin\Forms\ChangeItemPrice as ChangeItemPriceForm;
use Dcat\Admin\Widgets\Modal;
use App\Models\User;

class ChangeItemPrice extends AbstractTool
{
    /**
     * @return string
     */
	protected $title = '修改价格';

    public function render()
    {
        $form = ChangeItemPriceForm::make([
            "item_id" => $this->getKey()
        ]);
        
        $button = <<<EOT
<button class="btn btn-primary grid-refresh btn-mini" style="margin-right:3px">
    修改价格</span>
</button>
EOT;

        return Modal::make()
            ->lg()
            ->title($this->title)
            ->body($form)
            ->button($button);
    }

    /**
     * @return string|void
     */
    protected function href()
    {
        // return admin_url('auth/users');
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
