<?php
namespace dhammawheel\contactadminposts\acp;

use phpbb\config\config;
use phpbb\language\language;
use phpbb\log\log;
use phpbb\request\request;
use phpbb\symfony_request;
use phpbb\template\template;
use phpbb\user;

/**
 * Contact Admin Posts ACP module.
 */
class cap_acp_module
{
    public $page_title;
    public $tpl_name;
    public $u_action;

    /** @var config */
    protected $config;

    /** @var template */
    protected $template;

    /** @var language */
    protected $language;

    /** @var user */
    protected $user;

    /** @var log */
    protected $log;

    /** @var request */
    protected $request;

    /** @var symfony_request */
    protected $symfony_request;

    /** @var array $errors */
    protected $errors = [];

    private const form_key_name = 'dhammawheel_contactadminposts_acp';

    public function main($id, $mode)
    {
        global $phpbb_container;
        $this->config = $phpbb_container->get('config');
        $this->template = $phpbb_container->get('template');
        $this->language = $phpbb_container->get('language');
        $this->user = $phpbb_container->get('user');
        $this->log = $phpbb_container->get('log');
        $this->request = $phpbb_container->get('request');
        $this->symfony_request = $phpbb_container->get('symfony_request');

        $this->tpl_name = 'cap_acp_settings';

        // Create a form key for preventing CSRF attacks
        add_form_key(self::form_key_name);

        if ($mode === 'contactadminposts') {
            if ($this->request->is_set_post('submit')) {
                $this->save_settings();
            }
            $this->display_settings();
        }
    }

    public function save_settings() {
        if (!check_form_key(self::form_key_name)) {
            $this->errors[] = $this->language->lang('FORM_INVALID');
        }
        if ($this->display_errors()) {
            return;
        }
        $config_arr = $this->request->variable('config', ['' => ''], true);
        // Set the options the user configured
        $this->config->set('dhammawheel_contactadminposts_target_forum_id', $config_arr['contactadminposts_target_forum_id']);
        // Add option settings change action to the admin log
        $this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_ACP_CONTACTADMINPOSTS_SETTINGS');
        // Option settings have been updated and logged
        // Confirm this to the user and provide link back to previous page
        trigger_error($this->language->lang('ACP_CONTACTADMINPOSTS_SETTING_SAVED') . adm_back_link($this->u_action));
    }

    public function display_settings() {
        $has_errors = (bool) count($this->errors);
        $this->template->assign_vars([
            'S_ERROR'		=> $has_errors,
            'ERROR_MSG'		=> $has_errors ? implode('<br />', $this->errors) : '',
            'U_ACTION'		=> $this->u_action,
            'CONTACTADMINPOSTS_TARGET_FORUM_ID'	=> $this->config['dhammawheel_contactadminposts_target_forum_id'],
        ]);
    }

    public function display_errors() {
        $has_errors = (bool) count($this->errors);
        $this->template->assign_vars([
            'S_ERROR'		=> $has_errors,
            'ERROR_MSG'		=> $has_errors ? implode('<br />', $this->errors) : '',
        ]);
        return $has_errors;
    }
}
