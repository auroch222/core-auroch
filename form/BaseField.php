<?php

namespace auroch\phpmvc\form;

use auroch\phpmvc\Model;

abstract class BaseField
{
    abstract public function renderInput(): string;

    public Model $model;
    public string $attribute;


    /**
     * @param Model $model
     * @param string $attribute
     */
    public function __construct(Model $model, string $attribute)
    {
        $this->model = $model;
        $this->attribute = $attribute;
    }


    public function __toString(): string
    {
        return sprintf('
                    <div class="col">
            <div class="form-group">
                <label>%s</label>
                %s
                <div class="invalid-feedback">%s</div>
            </div>
        </div>
        ',
            $this->model->getLabel($this->attribute),
            $this->renderInput(),
            $this->model->getFirstError($this->attribute)
        );
    }
}