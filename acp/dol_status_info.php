<?php
/**
*
* @package DOL Extension 0.0.1
* @copyright (c) 2016 Leodagan
* @license MIT
*
*/
namespace dol\status\acp;

class dol_status_info
{
	public function module()
	{
		return array(
			'filename'	=> '\dol\status\acp\dol_status_module',
			'title'		=> 'ACP_DOL_STATUS',
			'modes'		=> array(
				'settings'	=> array(
					'title' => 'ACP_DOL_STATUS_SETTINGS',
					'auth' => 'acl_a_server',
					'cat' => array('ACP_DOL_STATUS')
				),
			),
		);
	}
}