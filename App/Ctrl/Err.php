<?php

namespace App\Ctrl;

use App\View;

class Err extends Ctrl
{
    static $pageTitle = 'Error Occured';
    
    public function __construct($html, $status=null, $title=null)
    {
        if ( $title ) {
            self::$pageTitle = $title;
        }
        
        return View::setController($this)->setStatus($status)->make($html);
    }
}