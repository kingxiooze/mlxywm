<?php

namespace App\Admin\Controllers;

use App\Models\CustomerService;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class CustomerServiceController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new CustomerService(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('icon')->image('', 50, 50);
            $grid->column('address');
            $grid->column('account');
            $grid->column("salesman_code");
            $grid->column('service_type', "服务类型");
            $grid->model()->orderBy('id', 'desc');
        
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
                $filter->equal("salesman_code");
        
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
        return Show::make($id, new CustomerService(), function (Show $show) {
            $show->field('id');
            $show->field('icon');
            $show->field('address');
            $show->field('account');
            $show->field('created_at');
            $show->field('updated_at');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new CustomerService(), function (Form $form) {
            $form->image('icon')->autoUpload()->saveFullUrl()->required();
            $form->text('address');
            $form->text('account');
            $form->text("salesman_code");
            $form->text("service_type", "服务类型");
        });
    }
}
