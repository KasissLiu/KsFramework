<?php
/**
 * Created by PhpStorm.
 * User: kasiss
 * Date: 6/11/16
 * Time: 22:29
 */


class ErrorController extends KsfController
{

    public function ErrorAction()
    {
        // do something to show errors
            $e = $this->getError();
            echo $e->getMessage();
    }




}