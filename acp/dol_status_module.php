<?php
/**
*
* @package DOL Extension 0.0.1
* @copyright (c) 2016 Leodagan
* @license MIT
*
*/
namespace dol\status\acp;
class dol_status_module
{
	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;
	/** @var \phpbb\config\config */
	protected $config;
	/** @var \phpbb\config\db_text */
	protected $config_text;
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;
	/** @var \phpbb\log\log */
	protected $log;
	/** @var \phpbb\request\request */
	protected $request;
	/** @var \phpbb\template\template */
	protected $template;
	/** @var \phpbb\user */
	protected $user;
	/** @var string */
	protected $phpbb_root_path;
	/** @var string */
	protected $php_ext;
	/** @var string */
	public $u_action;
    
	public function main($id, $mode)
	{
		global $cache, $config, $db, $phpbb_log, $request, $template, $user, $phpbb_root_path, $phpEx, $phpbb_container;
		$this->cache = $cache;
		$this->config = $config;
		$this->config_text = $phpbb_container->get('config_text');
		$this->db = $db;
		$this->log = $phpbb_log;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $phpEx;

		// Add the DOL Status ACP lang file
		$this->user->add_lang_ext('dol/status', 'dolstatus_acp');
		// Load a template from adm/style for our ACP page
		$this->tpl_name = 'dol_status';
		// Set the page title for our ACP page
		$this->page_title = 'ACP_DOL_STATUS_SETTINGS';
		// Define the name of the form for use as a form key
		$form_name = 'acp_dol_status';
		add_form_key($form_name);
		// Set an empty error string
		$error = '';
		
        // Get all dol status data from the config_text table in the database
		$data = $this->config_text->get_array(array(
			'announcement_text',
			'announcement_uid',
			'announcement_bitfield',
			'announcement_options',
			'announcement_bgcolor',
		));
		// If form is submitted or previewed
		if ($this->request->is_set_post('submit'))
		{
			// Test if form key is valid
			if (!check_form_key($form_name))
			{
				$error = $this->user->lang('FORM_INVALID');
			}
		}

		// Output data to the template
		$this->template->assign_vars(array(
			'ERRORS'						=> $error,
		));
	}
}