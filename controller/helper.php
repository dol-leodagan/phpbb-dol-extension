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

class helper
{
    /* @var template */
    protected $template;
    
    /** @var \phpbb\cache\driver\driver_interface */
    protected $cache;

     /* @var config */
    protected $config;

    /** @var Symfony\Component\Yaml\Parser */
    protected $parser;
    
    /**
    * Constructor
    *
    * @param template $template
    */
    public function __construct(template $template, $cache, config $config)
    {
        $this->template = $template;
        $this->cache = $cache;
        $this->config = $config;
        $this->parser = new Parser();
    }
    
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
                return array('Y_Exception' => $e->getMessage());
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