<?php
/**
 *
 * Block Foes. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2025, Dhamma Wheel, https://www.dhammawheel.com/
 *
 */

namespace dhammawheel\blockfoes\event;

use phpbb\config\config;
use phpbb\controller\helper as controller_helper;
use phpbb\language\language;
use phpbb\notification\manager;
use phpbb\template\template;
use phpbb\db\driver\driver_interface;
use phpbb\user;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Block Foes Event listener.
 */
class main_listener implements EventSubscriberInterface
{
    /* @var driver_interface */
    protected $db;
    /* @var user */
    protected $user;
    /* @var \phpbb\language\language */
    protected $language;
    protected $table_prefix;

    public function __construct(driver_interface $db, user $user, language $language)
    {
        $this->db = $db;
        $this->user = $user;
        $this->language = $language;
        $this->table_prefix = $this->table_prefix;
    }

    /**
     * Map phpBB core events to the listener methods that should handle those events
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'core.user_setup'							=> 'load_language_on_setup',
            'core.viewtopic_modify_post_row' => 'hide_blocked_posts',
            // TODO(zds): Hide search results, too.
            // 'core.search_modify_post_row' => 'hide_blocked_search_results',
        ];
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
            'ext_name' => 'dhammawheel/blockfoes',
            'lang_set' => 'common',
        ];
        $event['lang_set_ext'] = $lang_set_ext;
    }

    private function get_users_who_block_me()
    {
        $blockers = [];

        if ($this->user->data['user_id'] == ANONYMOUS)
        {
            return $blockers;
        }

        $sql = 'SELECT user_id FROM ' . ZEBRA_TABLE . ' WHERE zebra_id = ' . (int) $this->user->data['user_id'] . ' AND foe = 1';
        $result = $this->db->sql_query($sql);
        while ($row = $this->db->sql_fetchrow($result))
        {
            $user_id = $row['user_id'];
            array_push($blockers, (int) $user_id);
        }
        $this->db->sql_freeresult($result);

        return $blockers;
    }

    public function hide_blocked_posts($event)
    {
        $post_row = $event['post_row'];
        $poster_id = $post_row['POSTER_ID'];

        $blockers = $this->get_users_who_block_me();

        $blocked = in_array($poster_id, $blockers);
        if ($blocked)
        {
            $post_row['MESSAGE'] = $this->language->lang('USER_HAS_BLOCKED_YOU');
            $post_row['POST_SUBJECT'] = 'Post hidden';
            $post_row['SIGNATURE'] =  '';
        }
        $post_row['S_IS_BLOCKED_BY_USER'] = $blocked;

        $event['post_row'] = $post_row;
    }

    // public function hide_blocked_search_results($event)
    // {
    //     $row = $event['row'];
    //     $poster_id = $row['poster_id'];

    //     $blockers = $this->get_users_who_block_me();

    //     $row['S_IS_BLOCKED_BY_USER'] = in_array($poster_id, $blockers);

    //     $event['row'] = $row;
    // }
}
