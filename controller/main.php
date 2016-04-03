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
use phpbb\request\request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
    
    /** Ladder Handler **/
    public function handle_ladder($cmd)
    {
        if ($cmd == 'guilds')
        {
            $ladder = $this->controller_helper->backend_yaml_query($cmd, 5 * 60);
            // Build Guilds Routes
            if (isset($ladder['Ladder']))
            {
                foreach ($ladder['Ladder'] as $key => $value)
                {
                    $ladder['Ladder'][$key]['LastPlayed'] = date('M j Y', $value['LastPlayed']);
                    $ladder['Ladder'][$key]['GUILD_URL'] = $this->helper->route('dol_herald_sheet', array('cmd' => 'guild', 'params' => $value['GuildName']));
                }
            }
             
            $this->controller_helper->assign_yaml_vars($ladder);
        }
        else
        {
            $ladder = array();
            
            $ladder = $this->controller_helper->backend_yaml_query($cmd, 5 * 60);
            
            // Build URL Routes
            if (isset($ladder['Ladder']))
            {
                foreach ($ladder['Ladder'] as $key => $value)
                {
                    $ladder['Ladder'][$key]['LastPlayed'] = date('M j Y', $value['LastPlayed']);
                    $ladder['Ladder'][$key]['PLAYER_URL'] = $this->helper->route('dol_herald_sheet', array('cmd' => 'player', 'params' => $value['PlayerName']));
                    if ($value['GuildName'] !== "")
                        $ladder['Ladder'][$key]['GUILD_URL'] = $this->helper->route('dol_herald_sheet', array('cmd' => 'guild', 'params' => $value['GuildName']));
                }
            }
            
            $this->controller_helper->assign_yaml_vars($ladder);
        }
        
        if ($cmd == 'albion' || $cmd == 'midgard' || $cmd == 'hibernia')
            $this->assign_class_uris($cmd);
        
        $this->template->assign_var('U_HERALD_COMMAND', $cmd);
        $this->template->assign_var('U_HERALD_ENABLE', true);
        return $this->helper->render('herald_body.html');
    }
    
    /** Class List Helper **/
    private function assign_class_uris($cmd, $params = '')
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
                        $classes['Classes'][$key][$num] = array('VALUE' => $item, 'URL' => $this->helper->route('dol_herald_search', array('cmd' => $cmd, 'params' => $item)));
                        $existing_classes[] = $item;
                    }
                }
            }
        }        
        $this->controller_helper->assign_yaml_vars($classes);
        
        if ($params !== "" && array_search($params, $existing_classes) === false)
            return false;
            
        return true;
    }
    
    /** Guild/Player Handler **/
    public function handle_sheet($cmd, $params)
    {
        if ($this->user->data['user_id'] == ANONYMOUS)
            return $this->helper->message('LOGIN_REQUIRED', array(), 'NO_AUTH_OPERATION', 403);
        
        if ($params === null || $params === '')
            return $this->handle_badsearch();
        
        if ($cmd == 'player')
        {
            $player_display = $this->controller_helper->backend_yaml_query('getplayer/'.$params, 5 * 60);
            //Build Routes and Stats
            if (isset($player_display['Player']))
            {
                if (isset($player_display['Player']['GuildName']) && $player_display['Player']['GuildName'] !== '')
                {
                    $player_display['Player']['GUILD_URL'] = $this->helper->route('dol_herald_sheet', array('cmd' => 'guild', 'params' => $player_display['Player']['GuildName']));
                    $player_display['Player']['BANNER_URL'] = $this->helper->route('dol_herald_images', array('cmd' => 'banner', 'params' => $player_display['Player']['GuildName']));
                }
                
                $player_display['Player']['SIGSMALL_URL'] = $this->helper->route('dol_herald_images', array('cmd' => 'sigsmall', 'params' => $params));
                $player_display['Player']['SIGSMALL_ABSURL'] = $this->helper->route('dol_herald_images', array('cmd' => 'sigsmall', 'params' => $params), true, false, UrlGeneratorInterface::ABSOLUTE_URL);
                $player_display['Player']['SIGDETAILED_URL'] = $this->helper->route('dol_herald_images', array('cmd' => 'sigdetailed', 'params' => $params));
                $player_display['Player']['SIGDETAILED_ABSURL'] = $this->helper->route('dol_herald_images', array('cmd' => 'sigdetailed', 'params' => $params), true, false, UrlGeneratorInterface::ABSOLUTE_URL);
                $player_display['Player']['SIGLARGE_URL'] = $this->helper->route('dol_herald_images', array('cmd' => 'siglarge', 'params' => $params));
                $player_display['Player']['SIGLARGE_ABSURL'] = $this->helper->route('dol_herald_images', array('cmd' => 'siglarge', 'params' => $params), true, false, UrlGeneratorInterface::ABSOLUTE_URL);
                
                // Stats
                $player_display['Player']['KILLSTOTAL'] = $player_display['Player']['KillsAlbionPlayers'] + $player_display['Player']['KillsMidgardPlayers'] + $player_display['Player']['KillsHiberniaPlayers'];
                $player_display['Player']['DEATHBLOWSTOTAL'] = $player_display['Player']['KillsAlbionDeathBlows'] + $player_display['Player']['KillsMidgardDeathBlows'] + $player_display['Player']['KillsHiberniaDeathBlows'];
                $player_display['Player']['SOLOTOTAL'] = $player_display['Player']['KillsAlbionSolo'] + $player_display['Player']['KillsMidgardSolo'] + $player_display['Player']['KillsHiberniaSolo'];
                
                $player_display['Player']['KILLSRATIODEATHBLOWS'] = round($player_display['Player']['DEATHBLOWSTOTAL'] / ($player_display['Player']['KILLSTOTAL'] == 0 ? 1 : $player_display['Player']['KILLSTOTAL']) * 100, 2);
                $player_display['Player']['KILLSRATIOSOLO'] = round($player_display['Player']['SOLOTOTAL'] / ($player_display['Player']['KILLSTOTAL'] == 0 ? 1 : $player_display['Player']['KILLSTOTAL']) * 100, 2);
                
                $player_display['Player']['KILLSRATIO_ALBION'] = round($player_display['Player']['KillsAlbionPlayers'] / ($player_display['Player']['KILLSTOTAL'] == 0 ? 1 : $player_display['Player']['KILLSTOTAL']) * 100, 2);
                $player_display['Player']['KILLSRATIO_MIDGARD'] = round($player_display['Player']['KillsMidgardPlayers'] / ($player_display['Player']['KILLSTOTAL'] == 0 ? 1 : $player_display['Player']['KILLSTOTAL']) * 100, 2);
                $player_display['Player']['KILLSRATIO_HIBERNIA'] = round($player_display['Player']['KillsHiberniaPlayers'] / ($player_display['Player']['KILLSTOTAL'] == 0 ? 1 : $player_display['Player']['KILLSTOTAL']) * 100, 2);

                $player_display['Player']['DEATHBLOWSRATIO_ALBION'] = round($player_display['Player']['KillsAlbionDeathBlows'] / ($player_display['Player']['DEATHBLOWSTOTAL'] == 0 ? 1 : $player_display['Player']['DEATHBLOWSTOTAL']) * 100, 2);
                $player_display['Player']['DEATHBLOWSRATIO_MIDGARD'] = round($player_display['Player']['KillsMidgardDeathBlows'] / ($player_display['Player']['DEATHBLOWSTOTAL'] == 0 ? 1 : $player_display['Player']['DEATHBLOWSTOTAL']) * 100, 2);
                $player_display['Player']['DEATHBLOWSRATIO_HIBERNIA'] = round($player_display['Player']['KillsHiberniaDeathBlows'] / ($player_display['Player']['DEATHBLOWSTOTAL'] == 0 ? 1 : $player_display['Player']['DEATHBLOWSTOTAL']) * 100, 2);
                
                $player_display['Player']['SOLORATIO_ALBION'] = round($player_display['Player']['KillsAlbionSolo'] / ($player_display['Player']['SOLOTOTAL'] == 0 ? 1 : $player_display['Player']['SOLOTOTAL']) * 100, 2);
                $player_display['Player']['SOLORATIO_MIDGARD'] = round($player_display['Player']['KillsMidgardSolo'] / ($player_display['Player']['SOLOTOTAL'] == 0 ? 1 : $player_display['Player']['SOLOTOTAL']) * 100, 2);
                $player_display['Player']['SOLORATIO_HIBERNIA'] = round($player_display['Player']['KillsHiberniaSolo'] / ($player_display['Player']['SOLOTOTAL'] == 0 ? 1 : $player_display['Player']['SOLOTOTAL']) * 100, 2);

                $player_display['Player']['KILLDEATHRATIO'] = round($player_display['Player']['KILLSTOTAL'] / ($player_display['Player']['DeathsPvP'] == 0 ? 1 : $player_display['Player']['DeathsPvP']), 2);
                $player_display['Player']['RPDEATHRATIO'] = round($player_display['Player']['RealmPoints'] / ($player_display['Player']['DeathsPvP'] == 0 ? 1 : $player_display['Player']['DeathsPvP']));
                
                $player_display['Player']['LastPlayed'] = date('M j Y', $player_display['Player']['LastPlayed']);
            }
            
            $this->controller_helper->assign_yaml_vars($player_display);
        }
        else if ($cmd == 'guild')
        {
            $guild_display = $this->controller_helper->backend_yaml_query('getguild/'.$params, 5 * 60);
            //Build Routes
            if (isset($guild_display['Guild']))
            {                    
                $guild_display['Guild']['BANNER_URL'] = $this->helper->route('dol_herald_images', array('cmd' => 'banner', 'params' => $guild_display['Guild']['Name']));
                if (isset($guild_display['Guild']['Players']) && is_array($guild_display['Guild']['Players']))
                {
                    foreach($guild_display['Guild']['Players'] as $num => $player)
                    {
                        $guild_display['Guild']['Players'][$num]['PLAYER_URL'] = $this->helper->route('dol_herald_sheet', array('cmd' => 'player', 'params' => $player['PlayerName']));
                        $guild_display['Guild']['Players'][$num]['LastPlayed'] = date('M j Y', $player['LastPlayed']);
                    }
                }
            }
            
            $this->controller_helper->assign_yaml_vars($guild_display);
        }
        
        $this->template->assign_var('U_HERALD_COMMAND', $cmd);
        $this->template->assign_var('U_HERALD_ENABLE', true);
        return $this->helper->render('herald_body.html');
    }
    
    /** Warmap Handler **/
    public function handle_warmap()
    {
        $warmap = $this->controller_helper->backend_yaml_query('warmap', 5 * 60);
        
        if (isset($warmap['Structures']))
        {
            foreach($warmap['Structures'] as $realm => $structures)
            {
                if (is_array($structures))
                {
                    foreach($structures as $num => $structure)
                    {
                        if (isset($structure['Claimed']) && $structure['Claimed'] === true)
                            $warmap['Structures'][$realm][$num]['IMGURL'] = $this->helper->route('dol_herald_images', array('cmd' => 'banner', 'params' => $structure['ClaimedBy']));

                        $warmap['Structures'][$realm][$num]['Since'] = $this->controller_helper->human_timing($structure['Since']).' '.$this->user->lang['DOL_STATUS_AGO'];
                    }
                }
            }
        }
                        
        
        $this->controller_helper->assign_yaml_vars($warmap);
        $this->template->assign_var('U_HERALD_COMMAND', '');
        $this->template->assign_var('U_HERALD_ENABLE', true);
        $this->template->assign_var('U_WARMAP_ENABLE', true);
        return $this->helper->render('herald_body.html');
    }
    
    /** Search Form Handler **/
    public function handle_searchform()
    {
        if ($this->user->data['user_id'] == ANONYMOUS)
            return $this->helper->message('LOGIN_REQUIRED', array(), 'NO_AUTH_OPERATION', 403);
        /** Redirect Search POST **/
        if ($this->request->is_set('herald_search'))
        {
            $search_string = $this->request->variable('herald_search', '', true);
            if (preg_match('/^([[:alnum:]À-ÿ ]+){3,}$/s', $search_string))
            {
                $headers = array('Location' => $this->helper->route('dol_herald_search', array('cmd' => 'search', 'params' => $search_string)));
                return new Response('', 303, $headers);
            }
        }
        return $this->handle_badsearch();
    }
    
    /** Search and Class Ladder **/
    public function handle($cmd, $params)
    {
        $ladder = array();
        
        /** Search **/
        if ($cmd == 'search')
        {
            if ($this->user->data['user_id'] == ANONYMOUS)
                return $this->helper->message('LOGIN_REQUIRED', array(), 'NO_AUTH_OPERATION', 403);
            if (strlen($params) > 2)
                $ladder = $this->controller_helper->backend_yaml_query($cmd.'/'.$params, 5 * 60);
            else
                $cmd = 'badsearch';
        }
        /** Realm / Classes **/
        else if ($cmd == 'albion' || $cmd == 'midgard' || $cmd == 'hibernia')
        {
            if ($this->assign_class_uris($cmd, $params))
                $ladder = $this->controller_helper->backend_yaml_query($cmd.'/'.$params, 5 * 60);
        }

        // Build URL Routes
        if (isset($ladder['Ladder']))
        {
            foreach ($ladder['Ladder'] as $key => $value)
            {
                $ladder['Ladder'][$key]['LastPlayed'] = date('M j Y', $value['LastPlayed']);
                $ladder['Ladder'][$key]['PLAYER_URL'] = $this->helper->route('dol_herald_sheet', array('cmd' => 'player', 'params' => $value['PlayerName']));
                if ($value['GuildName'] !== "")
                    $ladder['Ladder'][$key]['GUILD_URL'] = $this->helper->route('dol_herald_sheet', array('cmd' => 'guild', 'params' => $value['GuildName']));
            }
            $this->controller_helper->assign_yaml_vars($ladder);
        }
        
        $this->template->assign_var('U_HERALD_COMMAND', $cmd);
        $this->template->assign_var('U_HERALD_PARAM', $params);
        $this->template->assign_var('U_HERALD_ENABLE', true);

        return $this->helper->render('herald_body.html');
    }
    
    public function handle_badsearch($cmd, $params)
    {
        return $this->handle('badsearch', $params);
    }
    
    /** Book Handler **/
    public function handle_book()
    {
        return $this->helper->render('book_body.html');
    }
}
