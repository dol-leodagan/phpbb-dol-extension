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
use Symfony\Component\Yaml\Dumper;
use phpbb\cache\driver;
use phpbb\config\config;
use Symfony\Component\HttpFoundation\Response;
use phpbb\db\driver\driver_interface;
use phpbb\auth\auth;
use phpbb\user;
use phpbb\request\request;

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
    * phpBB db connector
    * @var \phpbb\db\driver\driver_interface
    */
    protected $db;
    
    /**
    * phpBB auth
    * @var \phpbb\auth\auth
    */
    protected $auth;
    
    /**
    * phpBB user
    * @var \phpbb\user
    */
    protected $user;

    /**
    * phpBB request
    * @var \phpbb\request\request
    */
    protected $request;

    /**
    * Extension root path
    * @var string
    */
    protected $root_path;

    /** @var Symfony\Component\Yaml\Parser */
    protected $parser;
    
    /** @var Symfony\Component\Yaml\Dumper */
    protected $dumper;
    
    /**
    * Constructor
    *
    * @param template $template
    */
    public function __construct(template $template, $cache, config $config, $phpbb_root_path, $db, $auth, $user, $request)
    {
        $this->template = $template;
        $this->cache = $cache;
        $this->config = $config;
        $this->phpbb_root_path = $phpbb_root_path;
        $this->root_path = $phpbb_root_path . 'ext/dol/status/';
        $this->db = $db;
        $this->auth = $auth;
        $this->user = $user;
        $this->request = $request;
        $this->parser = new Parser();
        $this->dumper = new Dumper();
    }
    
    /** Region Utils **/
    public function human_timing($timestamp)
    {
        $time = time() - $timestamp; // to get the time since that moment

        $tokens = array (
            31536000 => 'DOL_STATUS_YEAR',
            2592000 => 'DOL_STATUS_MONTH',
            604800 => 'DOL_STATUS_WEEK',
            86400 => 'DOL_STATUS_DAY',
            3600 => 'DOL_STATUS_HOUR',
            60 => 'DOL_STATUS_MINUTE',
            1 => 'DOL_STATUS_SECOND'
        );

        foreach ($tokens as $unit => $text) {
            if ($time < $unit) continue;
            $numberOfUnits = floor($time / $unit);
            return $numberOfUnits.' '.($this->user->lang[($text.( ( $numberOfUnits > 1 ) ? 'S' : '' ))]);
        }
    }
    
    public function seconds_time($timestamp)
    {
        $dtF = new \DateTime("@0");
        $dtT = new \DateTime("@$timestamp");
        
        // only seconds
        if ($timestamp < 60)
            return $dtF->diff($dtT)->format('%s '.($timestamp % 60 > 1 ? $this->user->lang['DOL_STATUS_SECONDS'] : $this->user->lang['DOL_STATUS_SECOND']));
        // only minutes
        if ($timestamp < 3600)
            return $dtF->diff($dtT)->format('%i '.(floor($timestamp / 60)  > 1 ? $this->user->lang['DOL_STATUS_MINUTES'] : $this->user->lang['DOL_STATUS_MINUTE']).', %s '.($timestamp % 60 > 1 ? $this->user->lang['DOL_STATUS_SECONDS'] : $this->user->lang['DOL_STATUS_SECOND']));
        // only hours
        if ($timestamp < 86400)
            return $dtF->diff($dtT)->format('%h '.(floor($timestamp / 3600)  > 1 ? $this->user->lang['DOL_STATUS_HOURS'] : $this->user->lang['DOL_STATUS_HOUR']).', %i '.(floor(($timestamp % 3600) / 60)  > 1 ? $this->user->lang['DOL_STATUS_MINUTES'] : $this->user->lang['DOL_STATUS_MINUTE']));
        // only days
        if ($timestamp < 31536000)
            return $dtF->diff($dtT)->format('%a '.(floor($timestamp / 86400)  > 1 ? $this->user->lang['DOL_STATUS_DAYS'] : $this->user->lang['DOL_STATUS_DAY']).', %h '.(floor(($timestamp % 86400) / 3600)  > 1 ? $this->user->lang['DOL_STATUS_HOURS'] : $this->user->lang['DOL_STATUS_HOUR']));
            
        return $dtF->diff($dtT)->format('%y '.(floor($timestamp / 31536000)  > 1 ? $this->user->lang['DOL_STATUS_YEARS'] : $this->user->lang['DOL_STATUS_YEAR']).', %a '.(floor(($timestamp % 31536000) / 86400)  > 1 ? $this->user->lang['DOL_STATUS_DAYS'] : $this->user->lang['DOL_STATUS_DAY']));
    }
    
    public function username_from_perm()
    {
        if ($this->user->data['user_perm_from'] && $this->auth->acl_get('a_switchperm'))
        {
			$sql = 'SELECT username FROM ' . USERS_TABLE . ' WHERE user_id = ' . $this->user->data['user_perm_from'];
			$result = $this->db->sql_query($sql);
			$user_row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);
            
            if ($user_row)
                return $user_row['username'];
        }
        
        return $this->user->data['username'];
    }
    /** EndRegion Utils **/
    
    /** Region BackendPost **/
    public function backend_yaml_post($service, $data, $cachettl, $count, $action = "default")
    {
        $username = 'GUEST';
        if ($this->user->data['user_id'] !== ANONYMOUS && $this->user->data['username'] != null && $this->user->data['username'] != '')
            $username = $this->user->data['username'];
        
        $cache_string = '_YMLBACKENDPOST_'.$username.'_'.$action;
        $cache_get = $this->cache->get($cache_string);
        
        if ($cache_get === FALSE || $cache_get['Yaml_Throttling']['Queries'] < $count)
        {
            $queries = $cache_get === FALSE ? 1 : $cache_get['Yaml_Throttling']['Queries'] + 1;
            $cache_new = array('Yaml_Throttling' => array('Queries' => $queries, 'Time' => time(), 'TTL' => $cachettl));

            $content = $this->backend_raw_post($service, $this->dumper->dump($data));
            try
            {
                $value = $this->parser->parse($content);
                $this->cache->put($cache_string, $cache_new, $cachettl);
                return $value;
            }
            catch (ParseException $e)
            {
                return array('Yaml_Exception' => $e->getMessage());
            }
        }
        
        return $cache_get;
    }
    
    protected function backend_raw_post($service, $data)
    {
        $backend_url = 'https://karadok.freyad.net/';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $backend_url.$service);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // The --cacert option
        curl_setopt($ch, CURLOPT_CAINFO, '/var/lib/openshift/56c899540c1e66e27c000049/app-root/data/ssl/server.cert');
        // The --cert option
        curl_setopt($ch, CURLOPT_SSLCERT, '/var/lib/openshift/56c899540c1e66e27c000049/app-root/data/ssl/client.pem');
        $raw_get = curl_exec($ch);
        curl_close($ch);
        return $raw_get;
    }

    /** Region BackendQuery **/
    public function backend_yaml_query($service, $cachettl)
    {
        $cache_get = $this->cache->get('_YMLBACKEND_'.$service);
        
        if ($cache_get === FALSE)
        {
            $content = $this->backend_raw_query($service);
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
    
    protected function backend_raw_query($service)
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
    
    public function backeng_yaml_query_purge($service)
    {
        $this->cache->destroy('_YMLBACKEND_'.$service);
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
    
    /** Region Form Handler **/
    public function create_hidden_input($filter = false)
    {
        $result = $this->walk_form_array(array($filter, 'creation_time', 'form_token'));
        return $result;
    }
    
    protected function walk_form_array($filter = array())
    {
        $values = $this->request->variable_names();
        $globals = $this->request->get_super_global();
        foreach ($values as $name)
        {
            $result .= $this->build_form_array($globals[$name], $name, $filter);
        }
        
        return $result;
    }
    
    protected function build_form_array($input, $name, $filter)
    {
        if (in_array($name, $filter))
            return '';

        if (is_array($input))
        {
            $result = '';
            foreach($input as $key => $val)
            {
                $result .= $this->build_form_array($val, $name.'['.$key.']', $filter);
            }
            return $result;
        }
        else
        {
            return '<input type="hidden" name="'.htmlentities($name).'" value="'.htmlentities($input).'" />';
        }
        return $result;
    }
}