<?php

namespace App\Admin\Actions\Grid;

use App\Admin\Forms\ChangeUserBalance as FormsChangeUserBalance;
use Dcat\Admin\Actions\Response;
use Dcat\Admin\Grid\RowAction;
use Dcat\Admin\Traits\HasPermissions;
use Dcat\Admin\Widgets\Modal;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ChangeUserBalance extends RowAction
{
    protected $title = "增减余额";

    public function render()
    {
        $form = FormsChangeUserBalance::make()
            ->payload(["user_id" => $this->getKey()]);

        return Modal::make()
            ->lg()
            ->title($this->title)
            ->body($form)
            ->button($this->title);
    }
}
