<?php

namespace App\SalesAdmin\Controllers;

use App\Admin\Actions\Grid\BatchCreateWhitelist;
use App\Models\UserSfen;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Http\Request;
use Dcat\Admin\Form\NestedForm;
use App\Admin\Forms\CopyTaskOrder;
class UserSfenController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $model = new UserSfen();
        return Grid::make($model, function (Grid $grid) {
            $grid->column('id')->sortable();
            // $grid->column('user_id');
            $grid->column('name', "姓名");
           
            
            
            $grid->model()->orderBy("id", "desc");

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
            });
        });
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        return Show::make($id, new UserSfen(), function (Show $show) {
            $show->field('id');
            $show->field('name');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new UserSfen(), function (Form $form) {
        $form->text('name', '姓名')->required();

    });
    }
}
