<?php

namespace App\Admin\Actions\Grid;

use Dcat\Admin\Actions\Response;
use Dcat\Admin\Grid\Tools\AbstractTool;
use Dcat\Admin\Traits\HasPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Admin\Forms\BatchCreateWhitelist as BatchCreateWhitelistForm;
use Dcat\Admin\Widgets\Modal;

class BatchCreateWhitelist extends AbstractTool
{
    /**
     * @return string
     */
	protected $title = "批量创建";

    public function render()
    {
        $form = BatchCreateWhitelistForm::make();

        $button = <<<EOT
<button class="btn btn-primary grid-refresh btn-mini" style="margin-right:3px">
    $this->title</span>
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
