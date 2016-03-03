<?php
/**
*
* @package DOL Extension 0.0.1
* @copyright (c) 2016 Leodagan
* @license MIT
*
*/
namespace dol\status\controller;
class main
{
    /**
    * PHP file extension
    * @var string
    */
    protected $php_ext;
    /**
    * Portal root path
    * @var string
    */
    protected $root_path;

    public function __construct($phpbb_root_path, $php_ext)
    {
        $this->phpbb_root_path = $phpbb_root_path;
        $this->php_ext = $php_ext;
        $this->root_path = $phpbb_root_path . 'ext/dol/status/';
    }
    
    public function handle()
    {
    }
}
