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
                if (preg_match('/^([[:alnum:]À-ÿ ]+)$/s', $search_string))
                    $headers = array('Location' => $this->helper->route('dol_status_controller', array('cmd' => 'search', 'params' => $search_string)));
                else
                    $headers = array('Location' => $this->helper->route('dol_status_badsearch', array('notcmd' => 'search', 'notparams' => $search_string)));

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
            if ($params !== '' && ($cmd == 'albion' || $cmd == 'midgard' || $cmd == 'hibernia'))
            {
                $ladder = $this->controller_helper->backend_yaml_query($cmd.'/'.$params, 5 * 60);
            }
            else if ($cmd == 'search')
            {
                // Prevent too short search
                if (strlen($params) > 2)
                    $ladder = $this->controller_helper->backend_yaml_query($cmd.'/'.$params, 5 * 60);
                else
                    $cmd = 'badsearch';
            }
            else
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
        else if ($cmd == 'guilds')
        {
            $ladder = $this->controller_helper->backend_yaml_query($cmd, 5 * 60);
            // Build Guilds Routes
            if (isset($ladder['Ladder']))
            {
                foreach ($ladder['Ladder'] as $key => $value)
                {
                    $ladder['Ladder'][$key]['LastPlayed'] = date('M j Y', $value['LastPlayed']);
                    $ladder['Ladder'][$key]['GUILD_URL'] = $this->helper->route('dol_status_controller', array('cmd' => 'guild', 'params' => $value['GuildName']));
                    if ($value['AllianceName'] !== "")
                        $ladder['Ladder'][$key]['ALLIANCE_URL'] = $this->helper->route('dol_status_controller', array('cmd' => 'guild', 'params' => $value['AllianceName']));
                }
            }
             
            $this->controller_helper->assign_yaml_vars($ladder);
        }
       
        /** Player and Guild Select **/
        if ($params !== '')
        {
            if ($cmd == 'player')
            {
                $player_display = $this->controller_helper->backend_yaml_query('getplayer/'.$params, 5 * 60);
                //Build Routes and Stats
                if (isset($player_display['Player']))
                {
                    if (isset($player_display['Player']['GuildName']) && $player_display['Player']['GuildName'] !== '')
                    {
                        $player_display['Player']['GUILD_URL'] = $this->helper->route('dol_status_controller', array('cmd' => 'guild', 'params' => $player_display['Player']['GuildName']));
                        $player_display['Player']['BANNER_URL'] = $this->helper->route('dol_status_controller', array('cmd' => 'banner', 'params' => $player_display['Player']['GuildName']));
                    }
                    
                    $player_display['Player']['SIGSMALL_URL'] = $this->helper->route('dol_status_controller', array('cmd' => 'sigsmall', 'params' => $params));
                    $player_display['Player']['SIGSMALL_ABSURL'] = $this->helper->route('dol_status_controller', array('cmd' => 'sigsmall', 'params' => $params), true, false, UrlGeneratorInterface::ABSOLUTE_URL);
                    $player_display['Player']['SIGDETAILED_URL'] = $this->helper->route('dol_status_controller', array('cmd' => 'sigdetailed', 'params' => $params));
                    $player_display['Player']['SIGDETAILED_ABSURL'] = $this->helper->route('dol_status_controller', array('cmd' => 'sigdetailed', 'params' => $params), true, false, UrlGeneratorInterface::ABSOLUTE_URL);
                    $player_display['Player']['SIGLARGE_URL'] = $this->helper->route('dol_status_controller', array('cmd' => 'siglarge', 'params' => $params));
                    $player_display['Player']['SIGLARGE_ABSURL'] = $this->helper->route('dol_status_controller', array('cmd' => 'siglarge', 'params' => $params), true, false, UrlGeneratorInterface::ABSOLUTE_URL);
                    
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
                }
                
                $this->controller_helper->assign_yaml_vars($player_display);
            }
            else if ($cmd == 'guild')
            {
                $guild_display = $this->controller_helper->backend_yaml_query('getguild/'.$params, 5 * 60);
                //Build Routes
                if (isset($guild_display['Guild']))
                {                    
                    $guild_display['Guild']['BANNER_URL'] = $this->helper->route('dol_status_controller', array('cmd' => 'banner', 'params' => $guild_display['Guild']['Name']));
                    if (isset($guild_display['Guild']['Players']) && is_array($guild_display['Guild']['Players']))
                    {
                        foreach($guild_display['Guild']['Players'] as $num => $player)
                        {
                            $guild_display['Guild']['Players'][$num]['PLAYER_URL'] = $this->helper->route('dol_status_controller', array('cmd' => 'player', 'params' => $player['PlayerName']));
                            $guild_display['Guild']['Players'][$num]['LastPlayed'] = date('M j Y', $player['LastPlayed']);
                        }
                    }
                }
                
                $this->controller_helper->assign_yaml_vars($guild_display);
            }
             /** Banner **/
            else if ($cmd == 'banner')
            {
                $headers = array(
                    'Content-Type'     => 'image/png',
                    'Content-Disposition' => 'inline; filename="'.$params.'"');
                return new Response($this->controller_helper->drawBanner($params), 200, $headers);
            }
            else if ($cmd == 'sigsmall')
            {
                $headers = array(
                    'Content-Type'     => 'image/png',
                    'Content-Disposition' => 'inline; filename="'.$params.'"');
               return new Response($this->controller_helper->drawSignatureSmall($params), 200, $headers);
            }
            else if ($cmd == 'sigdetailed')
            {
                $headers = array(
                    'Content-Type'     => 'image/png',
                    'Content-Disposition' => 'inline; filename="'.$params.'"');
               return new Response($this->controller_helper->drawSignatureDetailed($params), 200, $headers);
            }
            else if ($cmd == 'siglarge')
            {
                $headers = array(
                    'Content-Type'     => 'image/png',
                    'Content-Disposition' => 'inline; filename="'.$params.'"');
               return new Response($this->controller_helper->drawSignatureLarge($params), 200, $headers);
            }
        
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
