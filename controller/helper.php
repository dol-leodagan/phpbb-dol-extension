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

class helper
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
    
    /** Region - YAML to Template Parser **/
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