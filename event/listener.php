<?php
/**
*
* @package DOL Extension 0.0.1
* @copyright (c) 2016 Leodagan
* @license MIT
*
*/

namespace dol\status\event;

/**
 * Event listener
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{
    /** @var \dol\status\controller\main */
    protected $status_controller;
    /** @var \phpbb\auth\auth */
    protected $auth;
    /** @var \phpbb\config\config */
    protected $config;
    /** @var \phpbb\controller\helper */
    protected $controller_helper;
    /** @var \phpbb\path_helper */
    protected $path_helper;
    /** @var \phpbb\template\template */
    protected $template;
    /** @var \phpbb\user */
    protected $user;
    /** @var string phpEx */
    protected $php_ext;
    /**
    * Constructor of DOL Status event listener
    *
    * @param \dol\status\controller\main $status_controller DOL Status controller
    * @param \phpbb\auth\auth       $auth   phpBB auth object
    * @param \phpbb\config\config       $config phpBB config
    * @param \phpbb\controller\helper   $controller_helper  Controller helper object
    * @param \phpbb\path_helper     $path_helper        phpBB path helper
    * @param \phpbb\template\template   $template       Template object
    * @param \phpbb\user            $user           User object
    * @param string             $php_ext        phpEx
    */
    public function __construct(\dol\status\controller\main $status_controller, \phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\controller\helper $controller_helper, \phpbb\path_helper $path_helper, \phpbb\template\template $template, \phpbb\user $user, $php_ext)
    {
        $this->status_controller = $status_controller;
        $this->auth = $auth;
        $this->config = $config;
        $this->controller_helper = $controller_helper;
        $this->path_helper = $path_helper;
        $this->template = $template;
        $this->user = $user;
        $this->php_ext = $php_ext;
    }


    static public function getSubscribedEvents()
    {
        return array(
            'core.user_setup' => 'load_language_on_setup',
            'core.page_header' => 'add_status_link',
        );
    }

    public function load_language_on_setup($event)
    {
        $lang_set_ext = $event['lang_set_ext'];
        $lang_set_ext[] = array(
            'ext_name' => 'dol/status',
            'lang_set' => 'dolstatus',
        );
        $event['lang_set_ext'] = $lang_set_ext;
    }
    /**
    * Add status links
    *
    * @return null
    */
    public function add_status_link()
    {
        if (strpos($this->controller_helper->get_current_url(), '/herald') === false)
            $herald_link = $this->controller_helper->route('dol_status_controller');
        else
            $herald_link = $this->path_helper->remove_web_root_path($this->controller_helper->route('dol_status_controller'));
        /** Herald **/
        if (strpos($this->controller_helper->get_current_url(), '/herald/albion') === false)
            $herald_link_albion = $this->controller_helper->route('dol_herald_ladder', array('cmd' => 'albion'));
        else
            $herald_link_albion = $this->path_helper->remove_web_root_path($this->controller_helper->route('dol_herald_ladder', array('cmd' => 'albion')));
        if (strpos($this->controller_helper->get_current_url(), '/herald/midgard') === false)
            $herald_link_midgard = $this->controller_helper->route('dol_herald_ladder', array('cmd' => 'midgard'));
        else
            $herald_link_midgard = $this->path_helper->remove_web_root_path($this->controller_helper->route('dol_herald_ladder', array('cmd' => 'midgard')));
        if (strpos($this->controller_helper->get_current_url(), '/herald/hibernia') === false)
            $herald_link_hibernia = $this->controller_helper->route('dol_herald_ladder', array('cmd' => 'hibernia'));
        else
            $herald_link_hibernia = $this->path_helper->remove_web_root_path($this->controller_helper->route('dol_herald_ladder', array('cmd' => 'hibernia')));
        if (strpos($this->controller_helper->get_current_url(), '/herald/players') === false)
            $herald_link_players = $this->controller_helper->route('dol_herald_ladder', array('cmd' => 'players'));
        else
            $herald_link_players = $this->path_helper->remove_web_root_path($this->controller_helper->route('dol_herald_ladder', array('cmd' => 'players')));
        if (strpos($this->controller_helper->get_current_url(), '/herald/kills') === false)
            $herald_link_kills = $this->controller_helper->route('dol_herald_ladder', array('cmd' => 'kills'));
        else
            $herald_link_kills = $this->path_helper->remove_web_root_path($this->controller_helper->route('dol_herald_ladder', array('cmd' => 'kills')));
        if (strpos($this->controller_helper->get_current_url(), '/herald/guilds') === false)
            $herald_link_guilds = $this->controller_helper->route('dol_herald_ladder', array('cmd' => 'guilds'));
        else
            $herald_link_guilds = $this->path_helper->remove_web_root_path($this->controller_helper->route('dol_herald_ladder', array('cmd' => 'guilds')));
        if (strpos($this->controller_helper->get_current_url(), '/herald/solo') === false)
            $herald_link_solo = $this->controller_helper->route('dol_herald_ladder', array('cmd' => 'solo'));
        else
            $herald_link_solo = $this->path_helper->remove_web_root_path($this->controller_helper->route('dol_herald_ladder', array('cmd' => 'solo')));
        if (strpos($this->controller_helper->get_current_url(), '/herald/deathblow') === false)
            $herald_link_deathblow = $this->controller_helper->route('dol_herald_ladder', array('cmd' => 'deathblow'));
        else
            $herald_link_deathblow = $this->path_helper->remove_web_root_path($this->controller_helper->route('dol_herald_ladder', array('cmd' => 'deathblow')));
        if (strpos($this->controller_helper->get_current_url(), '/herald/active') === false)
            $herald_link_active = $this->controller_helper->route('dol_herald_ladder', array('cmd' => 'active'));
        else
            $herald_link_active = $this->path_helper->remove_web_root_path($this->controller_helper->route('dol_herald_ladder', array('cmd' => 'active')));
        if (strpos($this->controller_helper->get_current_url(), '/herald/search') === false)
            $herald_link_search = $this->controller_helper->route('dol_herald_searchform', array('cmd' => 'search'));
        else
            $herald_link_search = $this->path_helper->remove_web_root_path($this->controller_helper->route('dol_herald_searchform', array('cmd' => 'search')));
        
        
        
        if (strpos($this->controller_helper->get_current_url(), '/account') === false)
            $game_link = $this->controller_helper->route('dol_status_game');
        else
            $game_link = $this->path_helper->remove_web_root_path($this->controller_helper->route('dol_status_game'));
        
        if (strpos($this->controller_helper->get_current_url(), '/grimoire') === false)
            $book_link = $this->controller_helper->route('dol_status_book');
        else
            $book_link = $this->path_helper->remove_web_root_path($this->controller_helper->route('dol_status_book'));
        
        if (strpos($this->controller_helper->get_current_url(), '/status') === false)
            $status_link = $this->controller_helper->route('dol_status_status');
        else
            $status_link = $this->path_helper->remove_web_root_path($this->controller_helper->route('dol_status_status'));
        
        $this->template->assign_vars(array(
            'U_DOL_STATUS'   => true,
            'U_DOL_STATUS_HERALD'   => $herald_link,
            'U_DOL_STATUS_HERALD_SELECT'   => 'app/herald, herald',
            'U_HERALD_WARMAP'   => $herald_link,
            'U_HERALD_ALBION'   => $herald_link_albion,
            'U_HERALD_MIDGARD'   => $herald_link_midgard,
            'U_HERALD_HIBERNIA'   => $herald_link_hibernia,
            'U_HERALD_PLAYERS'   => $herald_link_players,
            'U_HERALD_GUILDS'   => $herald_link_guilds,
            'U_HERALD_KILLS'   => $herald_link_kills,
            'U_HERALD_SOLO'   => $herald_link_solo,
            'U_HERALD_DEATHBLOW'   => $herald_link_deathblow,
            'U_HERALD_ACTIVE'   => $herald_link_active,
            'U_HERALD_SEARCH'   => $herald_link_search,
            'U_DOL_STATUS_GAME'   => $game_link,
            'U_DOL_STATUS_GAME_SELECT'   => 'app/account, account',
            'U_DOL_STATUS_BOOK'   => $book_link,
            'U_DOL_STATUS_BOOK_SELECT'   => 'app/grimoire, grimoire, app/book, book',
            'U_DOL_STATUS_STATUS'   => $status_link,
            'U_DOL_STATUS_STATUS_SELECT'   => 'app/status, status',
        ));
    }

}
?>
