<?php

namespace Meiko\Crud;

interface CrudInterface
{
    public function getId() : int;
    public function setId($id);
}