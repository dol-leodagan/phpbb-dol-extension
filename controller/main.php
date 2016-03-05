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
use \phpbb\cache\driver;
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

    /** @var \phpbb\cache\driver\driver_interface */
    protected $cache;
    
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
    public function __construct(config $config, helper $helper, template $template, user $user, $phpbb_root_path, $php_ext, $cache)
    {
        $this->config = $config;
        $this->helper = $helper;
        $this->template = $template;
        $this->user = $user;
        $this->phpbb_root_path = $phpbb_root_path;
        $this->php_ext = $php_ext;
        $this->root_path = $phpbb_root_path . 'ext/dol/status/';
        $this->cache = $cache;
    }
    
    public function handle()
    {
        return $this->helper->render('herald_body.html');
    }
    
    public function handle_game()
    {
        return $this->helper->render('game_body.html');
    }
    
    public function handle_book()
    {
        return $this->helper->render('book_body.html');
    }
    public function handle_status()
    {
        return $this->helper->render('status_body.html');
    }
    public function handle_statusmini()
    {
        $status_mini = '<h2>this is status mini !</h2>';
        
        $status_mini = $this->cache->get('STATUS_MINI');
        
        if ($status_mini === FALSE)
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://karadok.freyad.net/serverstatus");
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            // The --cacert option
            curl_setopt($ch, CURLOPT_CAINFO, "/var/lib/openshift/56c899540c1e66e27c000049/app-root/data/ssl/server.cert");
           // The --cert option
           curl_setopt($ch, CURLOPT_SSLCERT, "/var/lib/openshift/56c899540c1e66e27c000049/app-root/data/ssl/client.pem");

           $status_mini = curl_exec($ch);
           curl_close($ch);
           $this->cache->put('STATUS_MINI', $status_mini, 45);
        }
        
        $this->template->assign_var('STATUS_MINI', $status_mini);
        return $this->helper->render('statusmini_body.html');
    }
    public function handle_statusrvrmini()
    {
        $status_mini = '<h2>this is status RvR mini !</h2>';
        
        $status_mini = $this->cache->get('STATUS_RVRMINI');
        
        if ($status_mini === FALSE)
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://karadok.freyad.net/serverrvrstatus");
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            // The --cacert option
            curl_setopt($ch, CURLOPT_CAINFO, "/var/lib/openshift/56c899540c1e66e27c000049/app-root/data/ssl/server.cert");
           // The --cert option
           curl_setopt($ch, CURLOPT_SSLCERT, "/var/lib/openshift/56c899540c1e66e27c000049/app-root/data/ssl/client.pem");

           $status_mini = curl_exec($ch);
           curl_close($ch);
           $this->cache->put('STATUS_RVRMINI', $status_mini, 45);
        }
        
        $this->template->assign_var('STATUS_RVRMINI', $status_mini);
        return $this->helper->render('statusrvrmini_body.html');
    }
}
