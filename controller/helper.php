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
use Symfony\Component\Yaml\Parser;
use phpbb\cache\driver;
use phpbb\config\config;
use Symfony\Component\HttpFoundation\Response;
use phpbb\db\driver\driver_interface;
use phpbb\auth\auth;

class helper
{
    /* @var template */
    protected $template;
    
    /** @var \phpbb\cache\driver\driver_interface */
    public $cache;

     /* @var config */
    protected $config;

    /**
    * phpBB root path
    * @var string
    */
    protected $phpbb_root_path;
    
    /**
    * phpBB root path
    * @var \phpbb\db\driver\driver_interface
    */
    protected $db;
    
    /**
    * phpBB root path
    * @var \phpbb\auth\auth
    */
    protected $auth;
    
    /**
    * Extension root path
    * @var string
    */
    protected $root_path;

    /** @var Symfony\Component\Yaml\Parser */
    protected $parser;
    
    /**
    * Constructor
    *
    * @param template $template
    */
    public function __construct(template $template, $cache, config $config, $phpbb_root_path, $db, $auth)
    {
        $this->template = $template;
        $this->cache = $cache;
        $this->config = $config;
        $this->phpbb_root_path = $phpbb_root_path;
        $this->root_path = $phpbb_root_path . 'ext/dol/status/';
        $this->db = $db;
        $this->auth = $auth;
        $this->parser = new Parser();
    }
    
    /** Region Utils **/
    public function human_timing($timestamp)
    {
        $time = time() - $timestamp; // to get the time since that moment

        $tokens = array (
            31536000 => 'year',
            2592000 => 'month',
            604800 => 'week',
            86400 => 'day',
            3600 => 'hour',
            60 => 'minute',
            1 => 'second'
        );

        foreach ($tokens as $unit => $text) {
            if ($time < $unit) continue;
            $numberOfUnits = floor($time / $unit);
            return $numberOfUnits.' '.$text.(($numberOfUnits>1)?'s':'');
        }
    }
    
    public function seconds_time($timestamp)
    {
        $dtF = new \DateTime("@0");
        $dtT = new \DateTime("@$timestamp");
        
        // only seconds
        if ($timestamp < 60)
            return $dtF->diff($dtT)->format('%s second'.($timestamp % 60 > 1 ? 's' : ''));
        // only minutes
        if ($timestamp < 3600)
            return $dtF->diff($dtT)->format('%i minute'.(floor($timestamp / 60)  > 1 ? 's' : '').', %s second'.($timestamp % 60 > 1 ? 's' : ''));
        // only hours
        if ($timestamp < 86400)
            return $dtF->diff($dtT)->format('%h hour'.(floor($timestamp / 3600)  > 1 ? 's' : '').', %i minute'.(floor(($timestamp % 3600) / 60)  > 1 ? 's' : ''));
        // only days
        if ($timestamp < 31536000)
            return $dtF->diff($dtT)->format('%a day'.(floor($timestamp / 86400)  > 1 ? 's' : '').', %h hour'.(floor(($timestamp % 86400) / 3600)  > 1 ? 's' : ''));
            
        return $dtF->diff($dtT)->format('%y year'.(floor($timestamp / 31536000)  > 1 ? 's' : '').', %a day'.(floor(($timestamp % 31536000) / 86400)  > 1 ? 's' : ''));
    }
    
    public function username_from_perm($user)
    {
        if ($user->data['user_perm_from'] && $this->auth->acl_get('a_switchperm'))
        {
			$sql = 'SELECT username FROM ' . USERS_TABLE . ' WHERE user_id = ' . $user->data['user_perm_from'];
			$result = $this->db->sql_query($sql);
			$user_row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);
            
            if ($user_row)
                return $user_row['username'];
        }
        
        return $user->data['username'];
    }
    /** EndRegion Utils **/
    
    /** Region BackendQuery **/
    public function backend_yaml_query($service, $cachettl)
    {
        $cache_get = $this->cache->get('_YMLBACKEND_'.$service);
        
        if ($cache_get === FALSE)
        {
            $content = $this->backend_raw_query($service, $cachettl);
            try
            {
                $value = $this->parser->parse($content);
                $this->cache->put('_YMLBACKEND_'.$service, $value, $cachettl);
                return $value;
            }
            catch (ParseException $e)
            {
                return array('Yaml_Exception' => $e->getMessage());
            }
        }
        
        return $cache_get;
    }
    
    protected function backend_raw_query($service, $cachettl)
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
        return $raw_get;
    }
    /** EndRegion BackendQuery **/


    /** Region - YAML to Template Parser **/
    public function assign_yaml_vars($yaml)
    {
        if (!is_array($yaml))
            return;
        
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
    /** EndRegion - YAML to Template Parser **/
}