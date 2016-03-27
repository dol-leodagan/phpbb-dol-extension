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
use \phpbb\request\request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class status
{
     /* @var config */
    protected $config;

    /* @var helper */
    protected $helper;

    /* @var template */
    protected $template;

    /* @var user */
    protected $user;
    
    /* @var request */
    protected $request;

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

    
    /** @var \dol\status\controller\helper */
    protected $controller_helper;
    
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
    public function __construct(config $config, helper $helper, template $template, user $user, request $request, $phpbb_root_path, $php_ext, $controller_helper)
    {
        $this->config = $config;
        $this->helper = $helper;
        $this->template = $template;
        $this->user = $user;
        $this->request = $request;
        $this->phpbb_root_path = $phpbb_root_path;
        $this->php_ext = $php_ext;
        $this->root_path = $phpbb_root_path . 'ext/dol/status/';
        $this->controller_helper = $controller_helper;
    }
    
    /** Status Handler **/
    public function handle()
    {
        $this->handle_mini();
        $this->handle_rvrmini();
        return $this->helper->render('status_body.html');
    }
    
    public function handle_mini()
    {
        $status_mini = $this->controller_helper->backend_yaml_query('serverstatus', 45);
        $this->controller_helper->assign_yaml_vars($status_mini);
        return $this->helper->render('statusmini_body.html');
        
    }
    
    public function handle_rvrmini()
    {
        $status_rvrmini = $this->controller_helper->backend_yaml_query('serverrvrstatus', 45);
        if (isset($status_rvrmini['CaptureLog']) && is_array($status_rvrmini['CaptureLog']))
            foreach($status_rvrmini['CaptureLog'] as $key => $log)
                if (isset($log['CaptureTime']))
                    $status_rvrmini['CaptureLog'][$key]['CaptureTime'] = $this->controller_helper->human_timing($log['CaptureTime']);
        
        $this->controller_helper->assign_yaml_vars($status_rvrmini);
        return $this->helper->render('statusrvrmini_body.html');
    }
}