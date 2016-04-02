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
        // Make sure account is identified
        $username = $this->user->data['username'];
        if ($this->user->data['user_id'] == ANONYMOUS || $username == null || $username == '')
            if ($this->request->is_ajax())
                return new JsonResponse(array('Status' => 'NOAUTH', 'Message' => $this->user->lang['LOGIN_REQUIRED'], 'Title' => $this->user->lang['NO_AUTH_OPERATION']), 403);
            else
                return $this->helper->message('LOGIN_REQUIRED', array(), 'NO_AUTH_OPERATION', 403);

        // Switched Permissions
        if ($this->user->data['user_perm_from'])
            $username = $this->controller_helper->username_from_perm();
        
        // Set Flags
        $this->template->assign_var('U_GAME_ENABLE', true);
        add_form_key('game_account_editing');

        // Handle Queries
        if ($response = $this->handle_queries($username))
            return $response;
        
        $account_display = $this->controller_helper->backend_yaml_query('getaccount/'.$username, 2 * 60);
        
        // Display Summary
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
        return $this->helper->render('game_body.html');
    }
    
    protected function handle_queries($alternate_user)
    {
        // Handle Post Queries
        if ($this->request->is_set('confirmpasswd'))
            return $this->handle_confirmpasswd($alternate_user);
        if ($this->request->is_set('thatnotme'))
            return $this->handle_thatnotme($alternate_user);
        if ($this->request->is_set('createacc_submit'))
            return $this->handle_createacc($alternate_user);
        if ($this->request->is_set('reset'))
            return $this->handle_reset($alternate_user);
        if ($this->request->is_set('kick'))
            return $this->handle_kick($alternate_user);
        if ($this->request->is_set('movetobind'))
            return $this->handle_bind($alternate_user);
        
        if ($this->request->is_ajax())
            return $this->handle_post_response(array('Status' => 'WARN', 'Message' => $this->user->lang['AJAX_ERROR_TEXT'], 'Title' => $this->user->lang['AJAX_ERROR_TITLE']), 200);
        
        return false;
    }
    
    /** Action Handlers **/
    protected function form_not_valid()
    {
        if (!check_form_key('game_account_editing'))
            return $this->handle_post_response(array('Status' => 'NOAUTH', 'Message' => $this->user->lang['FORM_INVALID'], 'Title' => $this->user->lang['ERROR']), 200);

        return false;
    }
    
    protected function form_not_confirmed($message, $extrainput = false, $extraname = false)
    {
        if (!$this->request->is_ajax() && !$this->request->is_set('status_form_confirm'))
        {
            // Display Confirmation Form
            $this->template->assign_var('CONFIRM_MESSAGE', $this->user->lang[$message]);
            $this->template->assign_var('REPEAT_INPUT', $this->controller_helper->create_hidden_input($extraname));
            
            if ($extrainput !== FALSE && $extraname !== false)
            {
                $input = '<input class="inputbox" type="'.($extrainput == 'password' ? 'text' : $extrainput).'" name="'.$extraname.'" required="required" autocomplete="off"/>';
                $this->template->assign_var('EXTRA_INPUT', $input);
            }
            
            return $this->helper->render('confirm_body.html');
        }

        return false;
    }
    
    protected function check_backend_post_reply($response)
    {  
        if (isset($response['Yaml_Throttling']))
        {
            return $this->handle_post_response(array('Status' => 'NOAUTH', 'Message' => sprintf($this->user->lang['DOL_STATUS_THROTTLED'], ceil(($response['Yaml_Throttling']['TTL'] - (time() - $response['Yaml_Throttling']['Time'])) / 60)), 'Title' => $this->user->lang['ERROR']), 200);
        }
        else if (!isset($response['QueryResult']) || $response === false)
        {
            return $this->handle_post_response(array('Status' => 'ERR', 'Message' => $this->user->lang['DOL_STATUS_BACKENDINV'], 'Title' => $this->user->lang['ERROR']), 200);
        }
        else if (isset($response['QueryResult']))
        {
             if ($response['QueryResult'] == 'invalid')
                return $this->handle_post_response(array('Status' => 'ERR', 'Message' => $this->user->lang['DOL_STATUS_INVALID'], 'Title' => $this->user->lang['ERROR']), 200);
             if ($response['QueryResult'] == 'error')
                return $this->handle_post_response(array('Status' => 'ERR', 'Message' => $this->user->lang['DOL_STATUS_BACKENDERR'], 'Title' => $this->user->lang['ERROR']), 200);
             if ($response['QueryResult'] == 'noupdate')
                return $this->handle_post_response(array('Status' => 'ERR', 'Message' => $this->user->lang['DOL_STATUS_BACKENDNOUP'], 'Title' => $this->user->lang['WARNINGS']), 200);
        }
        
        return true;
    }
    
    protected function handle_confirmpasswd($alternate_user)
    {
        if ($checkresponse = $this->form_not_valid())
            return $checkresponse;            
        
        $confirm_vars = $this->request->variable('confirmpasswd', array('' => ''), true);
        
        list($confirm_account, $confirm_password) = each($confirm_vars);
        
        if (!preg_match('/^.{1,20}$/su', $confirm_password))
            return $this->handle_post_response(array('Status' => 'WARN', 'Message' => $this->user->lang['DOL_GAME_MESSAGE_ENTERPASSWD']), 200);
        
        if ($confirm_account == '')
            return $this->handle_post_response(array('Status' => 'WARN', 'Message' => $this->user->lang['DOL_GAME_MESSAGE_WRONGFORM']), 200);

        if ($checkconfirm = $this->form_not_confirmed('DOL_GAME_ACTION_PASSWD_CONFIRM', 'password', 'confirmpasswd['.$confirm_account.']'))
            return $checkconfirm;
        
        $response = $this->controller_helper->backend_yaml_post('postaccount', array('ConfirmPendingPassword' => (array('Profile' => $alternate_user, 'Account' => $confirm_account, 'Password' => $confirm_password))), 5 * 60, 3, 'confirmpasswd');
        
        if (($check = $this->check_backend_post_reply($response)) !== true)
            return $check;

        if ($response['QueryResult'] == 'wrongpassword')
            return $this->handle_post_response(array('Status' => 'ERR', 'Message' => $this->user->lang['DOL_GAME_MESSAGE_WRONGPASSWD'], 'Title' => $this->user->lang['ERROR']), 200);
        
        // If Message was OK purge Cache
        $this->controller_helper->backeng_yaml_query_purge('getaccount/'.$alternate_user);
        return $this->handle_post_response(array('Status' => 'OK', 'Message' => $this->user->lang['DOL_GAME_ACTION_PASSWD_SUCCESS'], 'Title' => $this->user->lang['DOL_STATUS_SUCCESS']), 200);
    }
    
    protected function handle_thatnotme($alternate_user)
    {
        if ($checkresponse = $this->form_not_valid())
            return $checkresponse;            
        
        $confirm_vars = $this->request->variable('thatnotme', array('' => ''), true);
        
        list($confirm_account, $dummy) = each($confirm_vars);
        
        if ($confirm_account == '')
            return $this->handle_post_response(array('Status' => 'WARN', 'Message' => $this->user->lang['DOL_GAME_MESSAGE_WRONGFORM']), 200);
        
        if ($checkconfirm = $this->form_not_confirmed('DOL_GAME_ACTION_NOTME_CONFIRM'))
            return $checkconfirm;
        
        $response = $this->controller_helper->backend_yaml_post('postaccount', array('RemovePendingAccount' => (array('Profile' => $alternate_user, 'Account' => $confirm_account))), 5 * 60, 3, 'thatnotme');
        
        if (($check = $this->check_backend_post_reply($response)) !== true)
            return $check;

        // If Message was OK purge Cache
        $this->controller_helper->backeng_yaml_query_purge('getaccount/'.$alternate_user);
        return $this->handle_post_response(array('Status' => 'OK', 'Message' => $this->user->lang['DOL_GAME_ACTION_NOTME_SUCCESS'], 'Title' => $this->user->lang['DOL_STATUS_SUCCESS']), 200);
    }
    
    protected function handle_createacc($alternate_user)
    {
        if ($checkresponse = $this->form_not_valid())
            return $checkresponse;            
        
        $account_name = $this->request->variable('createacc_name', '', true);
        $account_password = $this->request->variable('createacc_passwd', '', true);
        $account_confirm = $this->request->variable('createacc_confirm', '', true);
        $account_default = $this->request->variable('createacc_default', '', true);
        
        if (!preg_match('/^[[:alpha:]][[:alnum:]]{2,19}$/s', $account_name))
            return $this->handle_post_response(array('Status' => 'WARN', 'Message' => $this->user->lang['DOL_GAME_MESSAGE_ACCOUNTNAME']), 200);
        if ($account_password !== $account_confirm)
            return $this->handle_post_response(array('Status' => 'WARN', 'Message' => $this->user->lang['DOL_GAME_MESSAGE_ACCOUNTCONFIRM']), 200);
        if (!preg_match('/^.{6,20}$/su', $account_password))
            return $this->handle_post_response(array('Status' => 'WARN', 'Message' => $this->user->lang['DOL_GAME_MESSAGE_ACCOUNTPASSWD']), 200);
        if (!preg_match('/^'.$this->user->lang['DOL_STATUS_ALBION'].'|'.$this->user->lang['DOL_STATUS_MIDGARD'].'|'.$this->user->lang['DOL_STATUS_HIBERNIA'].'$/si', $account_default))
            return $this->handle_post_response(array('Status' => 'WARN', 'Message' => $this->user->lang['DOL_GAME_MESSAGE_ACCOUNTDEFAULT']), 200);
        
        if ($checkconfirm = $this->form_not_confirmed('DOL_GAME_CREATEACC_ALERT'))
            return $checkconfirm;
        
        $response = $this->controller_helper->backend_yaml_post('postaccount', array('CreateNewAccount' => (array('Profile' => $alternate_user, 'Account' => $account_name, 'Password' => $account_password, 'Realm' => $account_default, 'IP' => $this->user->ip))), 5 * 60, 3, 'createacc');
        
        if (($check = $this->check_backend_post_reply($response)) !== true)
            return $check;

        if ($response['QueryResult'] == 'duplicate')
            return $this->handle_post_response(array('Status' => 'ERR', 'Message' => $this->user->lang['DOL_GAME_CREATEACC_DUPLICATE'], 'Title' => $this->user->lang['ERROR']), 200);

        // If Message was OK purge Cache
        $this->controller_helper->backeng_yaml_query_purge('getaccount/'.$alternate_user);
        return $this->handle_post_response(array('Status' => 'OK', 'Message' => $this->user->lang['DOL_GAME_CREATEACC_SUCCESS'], 'Title' => $this->user->lang['DOL_STATUS_SUCCESS']), 200);
    }
    
    protected function handle_reset($alternate_user)
    {
        if ($checkresponse = $this->form_not_valid())
            return $checkresponse;            
        
        $confirm_vars = $this->request->variable('reset', array('' => ''), true);
        
        list($confirm_account, $dummy) = each($confirm_vars);
        
        if ($confirm_account == '')
            return $this->handle_post_response(array('Status' => 'WARN', 'Message' => $this->user->lang['DOL_GAME_MESSAGE_WRONGFORM']), 200);
        
        if ($checkconfirm = $this->form_not_confirmed('DOL_GAME_ACTION_RESET_CONFIRM'))
            return $checkconfirm;
        
        // Generate Password
        $password = gen_rand_string_friendly(10);
        
        $response = $this->controller_helper->backend_yaml_post('postaccount', array('ResetAccountPassword' => (array('Profile' => $alternate_user, 'Account' => $confirm_account, 'Password' => $password))), 5 * 60, 1, 'reset');
        
        if (($check = $this->check_backend_post_reply($response)) !== true)
            return $check;
        
        // Generate Mail
        include_once($this->phpbb_root_path . 'includes/functions_messenger' . $this->php_ext);
        $messenger = new \messenger(false);
        $messenger->template('@dol_status/email_reset_template', $lang);
        $messenger->to($this->user->data['user_email'], $this->user->data['username']);

        $messenger->assign_vars(array(
                'ACC_PASSWORD'    => $password,
                'ACC_USERNAME'    => $confirm_account,
            ));
        
        $messenger->send();
        
        return $this->handle_post_response(array('Status' => 'OK', 'Message' => $this->user->lang['DOL_GAME_ACTION_RESET_SUCCESS'], 'Title' => $this->user->lang['DOL_STATUS_SUCCESS']), 200);
    }
    
    protected function handle_kick($alternate_user)
    {
        if ($checkresponse = $this->form_not_valid())
            return $checkresponse;            
        
        $confirm_vars = $this->request->variable('kick', array('' => ''), true);
        
        list($confirm_account, $dummy) = each($confirm_vars);
        
        if ($confirm_account == '')
            return $this->handle_post_response(array('Status' => 'WARN', 'Message' => $this->user->lang['DOL_GAME_MESSAGE_WRONGFORM']), 200);
        
        if ($checkconfirm = $this->form_not_confirmed('DOL_GAME_ACTION_KICK_CONFIRM'))
            return $checkconfirm;
        
        $response = $this->controller_helper->backend_yaml_post('postaccount', array('KickAccount' => (array('Profile' => $alternate_user, 'Account' => $confirm_account))), 5 * 60, 3, 'kick');
        
        if (($check = $this->check_backend_post_reply($response)) !== true)
            return $check;

        return $this->handle_post_response(array('Status' => 'OK', 'Message' => $this->user->lang['DOL_GAME_ACTION_KICK_SUCCESS'], 'Title' => $this->user->lang['DOL_STATUS_SUCCESS']), 200);
    }
    
    protected function handle_bind($alternate_user)
    {
        if ($checkresponse = $this->form_not_valid())
            return $checkresponse;            
        
        $confirm_vars = $this->request->variable('movetobind', array('' => array ('' => '')), true);
        
        list($confirm_account, $confirm_array) = each($confirm_vars);
        list($confirm_player, $dummy) = each($confirm_array);
        
        
        if ($confirm_account == '' || $confirm_player == '')
            return $this->handle_post_response(array('Status' => 'WARN', 'Message' => $this->user->lang['DOL_GAME_MESSAGE_WRONGFORM']), 200);
        
        if ($checkconfirm = $this->form_not_confirmed('DOL_GAME_ACTION_BIND_CONFIRM'))
            return $checkconfirm;
        
        $response = $this->controller_helper->backend_yaml_post('postaccount', array('MovePlayerToBind' => (array('Profile' => $alternate_user, 'Account' => $confirm_account, 'Character' => $confirm_player))), 5 * 60, 3, 'bind');
        
        if (($check = $this->check_backend_post_reply($response)) !== true)
            return $check;

        return $this->handle_post_response(array('Status' => 'OK', 'Message' => $this->user->lang['DOL_GAME_ACTION_BIND_SUCCESS'], 'Title' => $this->user->lang['DOL_STATUS_SUCCESS']), 200);
    }
    
    /** Response Handlers **/
    protected function handle_post_response($response_data, $status)
    {
        if ($this->request->is_ajax())
        {
            if (isset($response_data['Title']))
                return new JsonResponse(array('Status' => $response_data['Status'], 'Message' => $response_data['Message'], 'Title' => $response_data['Title']), $status);
            
            if ($response_data['Status'] == 'WARN')
                return new JsonResponse(array('Status' => $response_data['Status'], 'Message' => $response_data['Message'], 'Title' => $this->user->lang['WARNINGS']), $status);
            
            return new JsonResponse(array('Status' => $response_data['Status'], 'Message' => $response_data['Message']), $status);
        }
        else
        {
            if ($response_data['Status'] == 'ERR' || $response_data['Status'] == 'OK')
                return $this->helper->message($response_data['Message'].'<div class="status-center"><a href="'.$this->helper->route('dol_status_game').'" class="button2">'.$this->user->lang['DOL_GAME_ACTION_CLOSE'].'</a></div>', array(), $response_data['Title'], $status);
            
            
            if ($response_data['Status'] == 'WARN' || $response_data['Status'] == 'NOAUTH')
            {
                $this->template->assign_var('WARN_MESSAGE', $response_data['Message']);
                $this->template->assign_var('WARN_TITLE', isset($response_data['Title']) && $response_data['Title'] != '' ? $response_data['Title'] : $this->user->lang['WARNINGS']);
            }
            
            return false;
        }
    }
    
}
