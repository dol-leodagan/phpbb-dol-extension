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
    
    /** Herald Handler **/
    public function handle($cmd, $params)
    {
        /** Redirect Search POST **/
        if ($cmd == 'search' && $this->request->is_set('herald_search'))
        {
            $search_string = $this->request->variable('herald_search', '', true);

            if ($search_string !== '')
            {
                $headers = array('Location' => $this->helper->route('dol_status_controller', array('cmd' => 'search', 'params' => $search_string)));
                return new Response('', 303, $headers);
            }
        }
        
        /** Warmap **/
        $this->template->assign_var('U_HERALD_ENABLE', true);
        if ($cmd == 'warmap' || $cmd === '') {
            $this->template->assign_var('U_WARMAP_ENABLE', true);
            $warmap = $this->controller_helper->backend_yaml_query('warmap', 5 * 60);
            
            if (isset($warmap['Structures']))
                foreach($warmap['Structures'] as $realm => $structures)
                    if (is_array($structures))
                        foreach($structures as $num => $structure)
                            if (isset($structure['Claimed']) && $structure['Claimed'] === true)
                                $warmap['Structures'][$realm][$num]['IMGURL'] = $this->helper->route('dol_status_controller', array('cmd' => 'banner', 'params' => $structure['ClaimedBy']));
                            
            
            $this->controller_helper->assign_yaml_vars($warmap);
        }
        
        /** Realm / Classes **/
        if ($cmd == 'albion' || $cmd == 'midgard' || $cmd == 'hibernia')
        {
            $classes = $this->controller_helper->backend_yaml_query('classes', 24 * 60 * 60);
            $existing_classes = array();
            // Build URL Routes
            if (isset($classes['Classes']))
            {
                foreach ($classes['Classes'] as $key => $value)
                {
                    if (is_array($value))
                    {
                        foreach($value as $num => $item)
                        {
                            $classes['Classes'][$key][$num] = array('VALUE' => $item, 'URL' => $this->helper->route('dol_status_controller', array('cmd' => $cmd, 'params' => $item)));
                            $existing_classes[] = $item;
                        }
                    }
                }
            }        
            $this->controller_helper->assign_yaml_vars($classes);
            
            if ($params !== "" && array_search($params, $existing_classes) === false)
                $params = "";
        }
        
        /** Ladders **/
        if ($cmd == 'active' || $cmd == 'albion' || $cmd == 'midgard' || $cmd == 'hibernia' || $cmd == 'players' || $cmd == 'kills' || $cmd == 'solo' || $cmd == 'deathblow' || $cmd == 'search')
        {
            $ladder = array();
            
            // Filter Request Type from Command
            if ($params !== '' && ($cmd == 'albion' || $cmd == 'midgard' || $cmd == 'hibernia' || $cmd == 'search'))
            {
                $ladder = $this->controller_helper->backend_yaml_query($cmd.'/'.$params, 5 * 60);
            }
            else if ($cmd != 'search')
            {
                $ladder = $this->controller_helper->backend_yaml_query($cmd, 5 * 60);
            }
            
            // Build URL Routes
            if (isset($ladder['Ladder']))
            {
                foreach ($ladder['Ladder'] as $key => $value)
                {
                    $ladder['Ladder'][$key]['LastPlayed'] = date('M j Y', $value['LastPlayed']);
                    $ladder['Ladder'][$key]['PLAYER_URL'] = $this->helper->route('dol_status_controller', array('cmd' => 'player', 'params' => $value['PlayerName']));
                    if ($value['GuildName'] !== "")
                        $ladder['Ladder'][$key]['GUILD_URL'] = $this->helper->route('dol_status_controller', array('cmd' => 'guild', 'params' => $value['GuildName']));
                }
            }
            
            $this->controller_helper->assign_yaml_vars($ladder);
        }

        
        /** Banner **/
        if ($cmd == 'banner' && $params !== '')
        {
            return $this->controller_helper->drawBanner($params);
        }
        
        $this->template->assign_var('U_HERALD_COMMAND', $cmd);
        $this->template->assign_var('U_HERALD_PARAM', $params);

        /** Debug **/
        $arr = (array)$this->template;
        $this->template->assign_var('Y_DEBUG_DUMP', print_r($arr["\0*\0context"], 1)."\n\n");
        return $this->helper->render('herald_body.html');
    }
    
    /** Game Account Handler **/
    public function handle_game()
    {
        return $this->helper->render('game_body.html');
    }
    
    /** Book Handler **/
    public function handle_book()
    {
        return $this->helper->render('book_body.html');
    }

    /** Status Handler **/
    public function handle_status($type = 'all')
    {
        $template_name = 'status_body.html';
        switch($type)
        {
            case 'mini':
                $status_mini = $this->controller_helper->backend_yaml_query('serverstatus', 45);
                $this->controller_helper->assign_yaml_vars($status_mini);
                $template_name = 'statusmini_body.html';
            break;
            case 'rvrmini':
                $status_rvrmini = $this->controller_helper->backend_yaml_query('serverrvrstatus', 45);
                $this->controller_helper->assign_yaml_vars($status_rvrmini);
                $template_name = 'statusrvrmini_body.html';
            break;
            case 'all':
            default:
                $status_mini = $this->controller_helper->backend_yaml_query('serverstatus', 45);
                $this->controller_helper->assign_yaml_vars($status_mini);
                $status_rvrmini = $this->controller_helper->backend_yaml_query('serverrvrstatus', 45);
                $this->controller_helper->assign_yaml_vars($status_rvrmini);
            break;
        }
        
        /** Debug
        $arr = (array)$this->template;
        $this->template->assign_var('Y_DEBUG_DUMP', print_r($arr["\0*\0context"], 1)."\n\n"); **/
        return $this->helper->render($template_name);
    }
}
