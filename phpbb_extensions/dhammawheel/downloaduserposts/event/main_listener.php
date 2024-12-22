<?php
/**
 *
 * Download User Posts. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2024, Dhamma Wheel
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace dhammawheel\downloaduserposts\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Download User Posts Event listener.
 */
class main_listener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'core.user_setup'							=> 'load_language_on_setup',
            'core.ucp_display_module_before' => 'add_download_button',
            'core.permissions'	=> 'add_permissions',
        ];
    }

    /* @var \phpbb\language\language */
    protected $language;

    /* @var \phpbb\controller\helper */
    protected $helper;

    /* @var \phpbb\template\template */
    protected $template;

    /** @var string phpEx */
    protected $php_ext;

    /* @var \phpbb\auth\auth */
    protected $auth;

    public function __construct(\phpbb\language\language $language, \phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\auth\auth $auth, $php_ext)
    {
        $this->language = $language;
        $this->helper   = $helper;
        $this->template = $template;
        $this->auth = $auth;
        $this->php_ext  = $php_ext;
    }

    /**
     * Load common language files during user setup
     *
     * @param \phpbb\event\data	$event	Event object
     */
    public function load_language_on_setup($event)
    {
        $lang_set_ext = $event['lang_set_ext'];
        $lang_set_ext[] = [
            'ext_name' => 'dhammawheel/downloaduserposts',
            'lang_set' => 'common',
        ];
        $event['lang_set_ext'] = $lang_set_ext;
    }

    public function add_download_button() {
        $this->template->assign_vars([
            'U_DOWNLOADUSERPOSTS_DOWNLOAD_POSTS_ROUTE' => $this->helper->route('dhammawheel_downloaduserposts_controller'),
            'S_DOWNLOADUSERPOSTS_IS_ALLOWED' => $this->auth->acl_get('u_new_dhammawheel_downloaduserposts'),
        ]);
    }

    /**
     * Add permissions to the ACP -> Permissions settings page
     * This is where permissions are assigned language keys and
     * categories (where they will appear in the Permissions table):
     * actions|content|forums|misc|permissions|pm|polls|post
     * post_actions|posting|profile|settings|topic_actions|user_group
     *
     * Developers note: To control access to ACP, MCP and UCP modules, you
     * must assign your permissions in your module_info.php file. For example,
     * to allow only users with the a_new_dhammawheel_downloaduserposts permission
     * access to your ACP module, you would set this in your acp/main_info.php:
     *    'auth' => 'ext_dhammawheel/downloaduserposts && acl_a_new_dhammawheel_downloaduserposts'
     *
     * @param \phpbb\event\data	$event	Event object
     */
    public function add_permissions($event)
    {
        $permissions = $event['permissions'];

        $permissions['u_new_dhammawheel_downloaduserposts'] = ['lang' => 'ACL_U_NEW_DHAMMAWHEEL_DOWNLOADUSERPOSTS', 'cat' => 'profile'];

        $event['permissions'] = $permissions;
    }
}
