<?php
/**
 *
 * Download User Posts. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2024, Dhamma Wheel
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace dhammawheel\downloaduserposts\controller;

/**
 * Download User Posts main controller.
 */
class main_controller
{
    /** @var \phpbb\db\driver\driver_interface */
    protected $db;

    /** @var \phpbb\config\config */
    protected $config;

    /** @var \phpbb\controller\helper */
    protected $helper;

    /** @var \phpbb\template\template */
    protected $template;

    /** @var \phpbb\language\language */
    protected $language;

    /** @var \phpbb\user */
    protected $user;

    /** @var \phpbb\auth\auth */
    protected $auth;


    public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\config\config $config, \phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\language\language $language, \phpbb\user $user, \phpbb\auth\auth $auth)
    {
        $this->db = $db;
        $this->config	= $config;
        $this->helper	= $helper;
        $this->template	= $template;
        $this->language	= $language;
        $this->user = $user;
        $this->auth = $auth;
    }

    public function download()
    {
        if (!$this->user->data['is_registered'] || !$this->auth->acl_get('u_new_dhammawheel_downloaduserposts')) {
            trigger_error($this->language->lang('DOWNLOADUSERPOSTS_NO_AUTH'));
        }

        $sql = 'SELECT
                    p.post_id,
                    t.topic_id,
                    f.forum_name,
                    t.topic_title,
                    p.post_subject,
                    p.post_text,
                    from_unixtime(p.post_time, \'%Y-%m-%dT%H:%i:%SZ\') as post_time
                FROM ' . POSTS_TABLE . ' p
                JOIN ' . TOPICS_TABLE . ' t ON t.topic_id=p.topic_id
                JOIN ' . FORUMS_TABLE . ' f ON f.forum_id=p.forum_id
                WHERE p.poster_id = ' . (int) $this->user->data['user_id'] . '
                AND p.post_delete_time=0
                ORDER BY post_time DESC';
        $result = $this->db->sql_query($sql);
        $posts = $this->db->sql_fetchrowset($result);
        $this->db->sql_freeresult($result);

        $json_data = json_encode($posts, JSON_PRETTY_PRINT);

        $temp_file = tempnam(sys_get_temp_dir(), 'phpbb_posts_');
        file_put_contents($temp_file, $json_data);

        $zip = new \ZipArchive();
        $zip_file = $temp_file . '.zip';
        if ($zip->open($zip_file, \ZipArchive::CREATE) !== TRUE) {
            trigger_error('Could not create ZIP file');
        }
        $zip->addFile($temp_file, 'user_posts.json');
        $zip->close();

        header('Content-Type: application/zip');
        $filename = date('Ymd-His') . '_' . $this->user->data['username'] . '_posts.zip';
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($zip_file));
        readfile($zip_file);

        unlink($temp_file);
        unlink($zip_file);

        exit;
    }
}
