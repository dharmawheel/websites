<?php
namespace dhammawheel\contactadminposts\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Contact Admin Posts Event listener.
 */
class main_listener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'core.page_footer' => 'override_contact_link',
        ];
    }

    /* @var \phpbb\template\template */
    protected $template;

    public function __construct(\phpbb\template\template $template)
    {
        $this->template = $template;
    }


    public function override_contact_link($event) {
        $this->template->assign_vars([
            'U_CONTACT_US' => append_sid('./contact'),
        ]);
    }
}
