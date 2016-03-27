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
use Symfony\Component\HttpFoundation\JsonResponse;

class game
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
    
    /** Game Handler **/
    public function handle()
    {
        if ($this->request->is_ajax())
            return $this->handle_ajax();
       
        // Make sure account is identified
        $username = $this->user->data['username'];
        if ($this->user->data['user_id'] == ANONYMOUS || $username == null || $username == '')
            return $this->helper->message('LOGIN_REQUIRED', array(), 'NO_AUTH_OPERATION', 403);
                
        // Switched Permissions
        if ($this->user->data['user_perm_from'])
            $username = $this->controller_helper->username_from_perm();
        
        $account_display = $this->controller_helper->backend_yaml_query('getaccount/'.$username, 2 * 60);
        
        // Transform Dates
        if (isset($account_display['Account']))
        {
            if (is_array($account_display['Account']['Pending']))
            {
                foreach($account_display['Account']['Pending'] as $ind => $pend)
                {
                    if (isset($pend['LastLogin']))
                        $account_display['Account']['Pending'][$ind]['LastLogin'] = date('M j Y', $pend['LastLogin']);
                    if (isset($pend['CreationDate']))
                        $account_display['Account']['Pending'][$ind]['CreationDate'] = date('M j Y', $pend['CreationDate']);
                }
            }
            if (is_array($account_display['Account']['Validated']))
            {
                foreach($account_display['Account']['Validated'] as $ind => $valid)
                {
                    if (isset($valid['LastLogin']))
                        $account_display['Account']['Validated'][$ind]['LastLogin'] = date('M j Y', $valid['LastLogin']);
                    if (isset($valid['CreationDate']))
                        $account_display['Account']['Validated'][$ind]['CreationDate'] = date('M j Y', $valid['CreationDate']);
                    
                    if (is_array($valid['Players']))
                    {
                        foreach($valid['Players'] as $plid => $player)
                        {
                            if (isset($player['LastPlayed']))
                                $account_display['Account']['Validated'][$ind]['Players'][$plid]['LastPlayed'] = date('M j Y', $player['LastPlayed']);
                            if (isset($player['PlayedTime']))
                                $account_display['Account']['Validated'][$ind]['Players'][$plid]['PlayedTime'] = $this->controller_helper->seconds_time($player['PlayedTime']);

                            // Build URL Routes
                            if (isset($player['Name']))
                                $account_display['Account']['Validated'][$ind]['Players'][$plid]['PLAYER_URL'] = $this->helper->route('dol_herald_sheet', array('cmd' => 'player', 'params' => $player['Name']));
                            if (isset($player['GuildName']))
                                $account_display['Account']['Validated'][$ind]['Players'][$plid]['GUILD_URL'] = $this->helper->route('dol_herald_sheet', array('cmd' => 'guild', 'params' => $player['GuildName']));
                        }
                    }
                }
            }
        }
        $this->controller_helper->assign_yaml_vars($account_display);
        $this->template->assign_var('U_GAME_ENABLE', true);
        $this->template->assign_var('DEBUG_POST_DATA', print_r($this->request->get_super_global(), 1));
        add_form_key('game_account_editing');
        return $this->helper->render('game_body.html');
    }
    
    /** Ajax Handler **/
    protected function handle_ajax()
    {
        // Make sure account is identified
        $username = $this->user->data['username'];
        if ($this->user->data['user_id'] == ANONYMOUS || $username == null || $username == '')
            return new JsonResponse(array('Status' => 'NOAUTH', 'Message' => $this->user->lang['LOGIN_REQUIRED'], 'Title' => $this->user->lang['AJAX_ERROR_TITLE']), 403);
        
        if (!check_form_key('game_account_editing'))
            return new JsonResponse(array('Status' => 'NOAUTH', 'Message' => $this->user->lang['FORM_INVALID'], 'Title' => $this->user->lang['AJAX_ERROR_TITLE']), 200);

        // Switched Permissions
        if ($this->user->data['user_perm_from'])
            $username = $this->controller_helper->username_from_perm();

        if ($this->request->is_set('confirmpasswd'))
        {
            $confirm_vars = $this->request->variable('confirmpasswd', array('' => ''), true);
            
            list($confirm_account, $confirm_password) = each($confirm_vars);
            
            if ($confirm_password == '' || $confirm_account == '')
                return new JsonResponse(array('Status' => 'WARN', 'Message' => $this->user->lang['DOL_GAME_MESSAGE_ENTERPASSWD']), 200);
            
            $response = $this->controller_helper->backend_yaml_post('postaccount', array('ConfirmPendingPassword' => (array( 'Profile' => $username, 'Account' => $confirm_account, 'Password' => $confirm_password))), 5 * 60, 3, 'confirmpasswd_'.$username);
            
            if ($response === FALSE)
                return new JsonResponse(array('Status' => 'ERR', 'Message' => $this->user->lang['ERROR']), 200);
            else
                return new JsonResponse(array('Status' => 'OK', 'Message' => print_r($response, 1)), 200);
        }
        
        $status = 'OK';
        $message = print_r($this->request->get_super_global(), 1);
        $response = new JsonResponse(array('Status' => $status, 'Message' => $message), 200);
        return $response;
    }
}
