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
use phpbb\cache\driver;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Parser;

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
    
    public function handle($cmd, $params)
    {
        if ($cmd == "warmap" || $cmd == "")
            $this->template->assign_var('U_WARMAP_ENABLE', true);
        
        $this->template->assign_var('COMMAND', $cmd);
        $this->template->assign_var('PARAM', $params);

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
    public function handle_status($type = 'all')
    {
        switch($type)
        {
            case 'mini':
                $status_mini = $this->backend_yaml_query('serverstatus', 45);
                $this->assign_yaml_vars($status_mini);
                return $this->helper->render('statusmini_body.html');
            break;
            case 'rvrmini':
                $status_rvrmini = $this->backend_yaml_query('serverrvrstatus', 45);
                $this->assign_yaml_vars($status_rvrmini);
                return $this->helper->render('statusrvrmini_body.html');
            break;
            case 'all':
            default:
                $status_mini = $this->backend_yaml_query('serverstatus', 45);
                $this->assign_yaml_vars($status_mini);
                $status_rvrmini = $this->backend_yaml_query('serverrvrstatus', 45);
                $this->assign_yaml_vars($status_rvrmini);
                return $this->helper->render('status_body.html');
            break;
        }
    }
    
    protected function assign_yaml_vars($yaml)
    {
        foreach($yaml as $key => $value)
        {
            if (is_array($value))
            {
                if ($this->is_assoc($value))
                    $this->append_yaml_dictroot($value, 'Y_'.$key);
                else
                    $this->append_yaml_list($value, 'Y_'.$key);
            }
            else
            {
                $this->template->assign_var('Y_'.$key, $value);
            }
        }
        /** Debug **/
        $arr = (array)$this->template;
        $this->template->assign_var('Y_DEBUG_DUMP', print_r($arr["\0*\0context"], 1)."\n\n");
    }
    
    protected function append_yaml_dictroot($dict, $prefix)
    {
         foreach ($dict as $key => $value)
        {
            if (is_array($value))
            {
                if ($this->is_assoc($value))
                    $this->append_yaml_dictroot($value, $prefix.'_'.$key);
                else
                    $this->append_yaml_list($value, $prefix.'_'.$key);
            }
            else
            {
                $this->template->assign_var($prefix.'_'.$key, $value);
            }
        }
    }

    protected function append_yaml_list($list, $prefix)
    {
        foreach ($list as $key => $value)
        {
            if (is_array($value))
            {
                $this->template->assign_block_vars($prefix, array('KEY' => $key));

                if ($this->is_assoc($value))
                    $this->append_yaml_dict($value, $prefix);
                else
                    $this->append_yaml_list($value, $prefix.'.VALUE');
            }
            else
            {
                $this->template->assign_block_vars($prefix, array(
                    'KEY' => $key,
                    'VALUE' => $value
                ));
            }
        }
    }
    
    protected function append_yaml_dict($dict, $prefix, $next = false)
    {
        foreach ($dict as $key => $value)
        {
            $realkey = $next === FALSE ? $key : $next.'_'.$key;
            if (is_array($value))
            {
                if ($this->is_assoc($value))
                    $this->append_yaml_dict($value, $prefix, $realkey);
                else
                    $this->append_yaml_list($value, $prefix.'.'.$realkey);
            }
            else
            {
                $this->template->alter_block_array($prefix, array($realkey => $value), true, 'change');
            }
        }
    }
    
    protected function is_assoc($array)
    {
        return array_values($array)!==$array;
    }
    
    protected function backend_yaml_query($service, $cachettl)
    {
        $content = $this->backend_raw_query($service, $cachettl);
        $yaml = new Parser();
        try
        {
            $value = $yaml->parse($content);
            return $value;
        }
        catch (ParseException $e)
        {
            return array('Y_Exception' => $e->getMessage());
        }
    }
    
    protected function backend_raw_query($service, $cachettl)
    {
        $cache_get = $this->cache->get($service);
        
        if ($cache_get === FALSE)
        {
            $backend_url = 'https://karadok.freyad.net/';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $backend_url.$service);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            // The --cacert option
            curl_setopt($ch, CURLOPT_CAINFO, '/var/lib/openshift/56c899540c1e66e27c000049/app-root/data/ssl/server.cert');
            // The --cert option
            curl_setopt($ch, CURLOPT_SSLCERT, '/var/lib/openshift/56c899540c1e66e27c000049/app-root/data/ssl/client.pem');
            $raw_get = curl_exec($ch);
            curl_close($ch);
            if ($raw_get !== FALSE)
            {
                $this->cache->put($service, $raw_get, $cachettl);
                $cache_get = $raw_get;
            }
       }
       
       return $cache_get;
    }
}
