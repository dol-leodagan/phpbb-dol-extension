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
    /** @var \board3\portal\controller\main */
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
    * Add status link if user is authed to see it
    *
    * @return null
    */
    public function add_status_link()
    {
        if (strpos($this->controller_helper->get_current_url(), '/herald') === false)
        {
            $herald_link = $this->controller_helper->route('dol_status_controller');
        }
        else
        {
            $herald_link = $this->path_helper->remove_web_root_path($this->controller_helper->route('dol_status_controller'));
        }
        $this->template->assign_vars(array(
            'U_DOL_STATUS_HERALD'   => $herald_link,
        ));
    }

}
?>
