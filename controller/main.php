<?php
/**
*
* @package DOL Extension 0.0.1
* @copyright (c) 2016 Leodagan
* @license MIT
*
*/
namespace dol\status\controller;

use phpbb\config\config;
use phpbb\controller\helper;
use phpbb\template\template;
use phpbb\user;
use Symfony\Component\HttpFoundation\Response;

class main
{
     /* @var config */
    protected $config;

    /* @var helper */
    protected $helper;

    /* @var template */
    protected $template;

    /* @var user */
    protected $user;

    /**
    * phpBB root path
    * @var string
    */
    protected $phpbb_root_path;
    
    /**
    * PHP file extension
    * @var string
    */
    protected $php_ext;
    /**
    * Extension root path
    * @var string
    */
    protected $root_path;

    /**
     * Constructor
     *
     * @param config $config
     * @param helper $helper
     * @param template $template
     * @param user $user
     * @param string $phpbb_root_path
     * @param string $php_ext
     */
    public function __construct(config $config, helper $helper, template $template, user $user, $phpbb_root_path, $php_ext)
    {
        $this->config = $config;
        $this->helper = $helper;
        $this->template = $template;
        $this->user = $user;
        $this->phpbb_root_path = $phpbb_root_path;
        $this->php_ext = $php_ext;
        $this->root_path = $phpbb_root_path . 'ext/dol/status/';
    }
    
    public function handle()
    {
        return $this->helper->render('herald_body.html', $name);
    }
}
