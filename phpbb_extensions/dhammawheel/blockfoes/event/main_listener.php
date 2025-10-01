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
    /* @var template */
    protected $template;

    public function __construct(driver_interface $db, user $user, template $template)
    {
        $this->db = $db;
        $this->user = $user;
        $this->template = $template;
    }

    /**
     * Map phpBB core events to the listener methods that should handle those events
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'core.viewtopic_modify_post_list_sql' => 'filter_blocked_posts',
            'core.viewforum_get_topic_data' => 'filter_blocked_topics',
            'core.viewforum_get_topic_ids_data' => 'filter_blocked_topic_ids',
            'core.search_get_posts_data' => 'filter_blocked_posts_in_search',
            'core.search_get_topic_data' => 'filter_blocked_topics_in_search',
            'core.viewonline_modify_sql' => 'filter_blocked_users_from_viewonline',
        ];
    }

    public function filter_blocked_posts($event) {
        if ($this->user->data['user_id'] == ANONYMOUS) {
            return;
        }

        $sql = $event['sql'];
        $current_user_id = (int) $this->user->data['user_id'];
        $where_pos = stripos($sql, 'WHERE');
        if ($where_pos == false) {
            return;
        }
        $join_sql = ' LEFT JOIN ' . ZEBRA_TABLE . ' z ON (z.user_id = p.poster_id AND z.zebra_id = ' . $current_user_id . ' AND z.foe = 1) ';
        $where_condition_sql = ' z.user_id IS NULL AND ';
        $sql_before_where = substr($sql, 0, $where_pos);
        $sql_after_where = substr($sql, $where_pos + 5);
        $new_sql = $sql_before_where . $join_sql . 'WHERE' . $where_condition_sql . $sql_after_where;
        $event['sql'] = $new_sql;
    }

    public function filter_blocked_posts_in_search($event) {
        if ($this->user->data['user_id'] == ANONYMOUS) {
            return;
        }

        $sql_array = $event['sql_array'];
        $current_user_id = (int) $this->user->data['user_id'];
        $sql_array['LEFT_JOIN'][] = [
            'FROM' => [ZEBRA_TABLE => 'z_block'],
            'ON' => 'z_block.user_id = p.poster_id AND z_block.zebra_id = ' . $current_user_id . ' AND z_block.foe = 1',
        ];
        $sql_array['WHERE'] .= ' AND z_block.user_id IS NULL';
        $event['sql_array'] = $sql_array;
    }

    public function filter_blocked_topics_in_search($event) {
        if ($this->user->data['user_id'] == ANONYMOUS) {
            return;
        }

        $current_user_id = (int) $this->user->data['user_id'];
        $sql_from = $event['sql_from'];
        $sql_where = $event['sql_where'];

        $sql_from .= ' LEFT JOIN ' . ZEBRA_TABLE . ' z_block_topic ON (z_block_topic.user_id = t.topic_poster AND z_block_topic.zebra_id = ' . $current_user_id . ' AND z_block_topic.foe = 1)';
        $sql_where .= ' AND z_block_topic.user_id IS NULL';

        $event['sql_from'] = $sql_from;
        $event['sql_where'] = $sql_where;
    }

    public function filter_blocked_topics($event) {
        if ($this->user->data['user_id'] == ANONYMOUS) {
            return;
        }

        $current_user_id = (int) $this->user->data['user_id'];
        $sql_array = $event['sql_array'];

        $sql_array['LEFT_JOIN'][] = [
            'FROM' => [ZEBRA_TABLE => 'z_block_forum_topic'],
            'ON' => 'z_block_forum_topic.user_id = t.topic_poster AND z_block_forum_topic.zebra_id = ' . $current_user_id . ' AND z_block_forum_topic.foe = 1',
        ];
        $sql_where = 'z_block_forum_topic.user_id IS NULL';
        if (array_key_exists('WHERE', $sql_array)) {
            $sql_array['WHERE'] .= ' AND ' . $sql_where;
        } else {
            $sql_array['WHERE'] = $sql_where;
        }

        $event['sql_array'] = $sql_array;
    }

    public function filter_blocked_topic_ids($event) {
        if ($this->user->data['user_id'] == ANONYMOUS) {
            return;
        }

        $current_user_id = (int) $this->user->data['user_id'];
        $sql_array = $event['sql_ary'];

        $sql_array['LEFT_JOIN'][] = [
            'FROM' => [ZEBRA_TABLE => 'z_block_forum_topic_2'],
            'ON' => 'z_block_forum_topic_2.user_id = t.topic_poster and z_block_forum_topic_2.zebra_id = ' . $current_user_id . ' AND z_block_forum_topic_2.foe = 1',
        ];
        $sql_array['WHERE'] .= ' AND z_block_forum_topic_2.user_id IS NULL';

        $event['sql_ary'] = $sql_array;
    }

    public function filter_blocked_users_from_viewonline($event) {
        if ($this->user->data['user_id'] == ANONYMOUS) {
            return;
        }

        $current_user_id = (int) $this->user->data['user_id'];
        $sql_array = $event['sql_ary'];

        if (!isset($sql_array['LEFT_JOIN'])) {
            $sql_array['LEFT_JOIN'] = [];
        }
        $sql_array['LEFT_JOIN'][] = [
            'FROM' => [ZEBRA_TABLE => 'z_block_online'],
            'ON' => 'z_block_online.user_id = u.user_id AND z_block_online.zebra_id = ' . $current_user_id . ' AND z_block_online.foe = 1',
        ];
        $sql_array['WHERE'] .= ' AND z_block_online.user_id IS NULL';

        $event['sql_ary'] = $sql_array;
    }
}
