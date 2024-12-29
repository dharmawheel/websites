<?php
namespace dhammawheel\contactadminposts\controller;

class contact_controller
{
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

    /** @var string */
    protected $php_ext;

    /** @var \phpbb\request\request */
    protected $request;

    /** @var string */
    protected $root_path;

    /**
     * Constructor
     *
     * @param \phpbb\config\config		$config		Config object
     * @param \phpbb\controller\helper	$helper		Controller helper object
     * @param \phpbb\template\template	$template	Template object
     * @param \phpbb\language\language	$language	Language object
     * @param \phpbb\user				$user		User object
     */
    public function __construct(
        \phpbb\config\config $config,
        \phpbb\controller\helper $helper,
        \phpbb\template\template $template,
        \phpbb\language\language $language,
        \phpbb\user $user,
        \phpbb\request\request $request,
        string $php_ext,
        string $root_path
    )
    {
        $this->config	= $config;
        $this->helper	= $helper;
        $this->template	= $template;
        $this->language	= $language;
        $this->user		= $user;
        $this->request	= $request;
        $this->php_ext = $php_ext;
        $this->root_path = $root_path;
    }

    /**
     * Controller handler for route /demo/{name}
     *
     * @param string $name
     *
     * @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
     */
    public function handle()
    {
        // Make sure the user’s language is loaded
        $this->user->add_lang_ext('dhammawheel/contactadminposts', 'common');

        // Prevent CSRF attacks
        add_form_key('dharmawheel_contactadminposts_contact');

        // Collect form data
        $submit = $this->request->is_set_post('submit');
        $subject = $this->request->variable('subject', '', true);
        $message = $this->request->variable('message', '', true);

        // If user logged in, pre-fill the form with their name and email
        if ($this->user->data['is_registered']) {
            $name = $this->user->data['username'];
            $email = $this->user->data['user_email'];
        } else {
            // If user submitted the form
            $name   = $this->request->variable('name', '', true);
            $email  = $this->request->variable('email', '', true);
        }

        if ($submit && $name && $email && $message)
        {
            $forum_id = $this->config['dhammawheel_contactadminposts_target_forum_id'];

            // Load posting functions if needed
            if (!function_exists('submit_post')) {
                include_once($this->root_path . 'includes/functions_posting.' . $this->php_ext);
            }

            // Build the post text
            $post_message = "Name: {$name}\n\n"
                            . "Email: {$email}\n\n"
                            . "IP: {$this->user->ip}\n\n";

            $post_message .= "\n\n---\n{$message}\n---";

            $poll = array(); // No poll

            $data = array(
                'forum_id'         => $forum_id,
                'icon_id'          => 0,
                'topic_title'      => $subject ?: 'Contact Form Submission',

                'enable_bbcode'    => true,
                'enable_smilies'   => false,
                'enable_urls'      => true,
                'enable_sig'       => false,

                'message'          => $post_message,
                'message_md5'      => md5($post_message),
                'bbcode_bitfield'  => '',
                'bbcode_uid'       => '',

                'post_edit_locked' => 1,
                'topic_status'     => 0,
                'topic_type'       => POST_NORMAL,

                'poster_ip'        => $this->user->ip,
                'post_time'        => time(),
                'forum_name'       => '',
                'enable_indexing'  => true,

                'force_approved_state' => ITEM_APPROVED,
                'force_visibility'     => ITEM_APPROVED,
            );

            // Submit the post (create new topic)
            submit_post('post', $data['topic_title'], 'Anonymous', POST_NORMAL, $poll, $data);

            // Show a success message or redirect
            $message = $this->user->lang('CONTACT_MESSAGE_SENT');
            return $this->helper->message($message, array(), $this->user->lang('CONTACT_US'));
        }

        // Otherwise, display the form template
        return $this->helper->render('contact_admin_body.html', $this->user->lang('CONTACT_US'));
    }
}
