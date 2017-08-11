<?php

/**
 * 错误接收控制器 
 * 所有类内抛出的错误
 * 都将由modules下controller里的
 * ErrorController ErrorAction 接收
 */
class ErrorController extends KsfController
{

    public function ErrorAction()
    {
        // do something to show errors
        $e = $this->getError();
        echo $e->getErrorMessage();
    }
}