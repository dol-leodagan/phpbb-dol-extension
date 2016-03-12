<?php
/**
*
* @package DOL Extension 0.0.1
* @copyright (c) 2016 Leodagan
* @license MIT
*
*/
namespace dol\status\controller;

use phpbb\template\template;

class controller_helper
{
    /* @var template */
    protected $template;

    /**
    * Constructor
    *
    * @param template $template
    */
    public function __construct(template $template)
    {
        $this->template = $template;
    }
    
}