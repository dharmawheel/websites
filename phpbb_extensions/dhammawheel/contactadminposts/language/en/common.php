<?php
if (!defined('IN_PHPBB'))
{
    exit;
}

if (empty($lang) || !is_array($lang))
{
    $lang = [];
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, [
    'CONTACTADMINPOSTS_EVENT'		=> ' :: Contactadminposts Event :: ',
    'CONTACT_US' => 'Contact us',
    'CONTACT_MESSAGE_SENT' => 'Thank you for your message. We will get back to you shortly.',
    'YOUR_NAME' => 'Your name',
    'YOUR_EMAIL_ADDRESS' => 'Your email address',
    'SUBJECT' => 'Subject',
    'MESSAGE' => 'Message',
    'SUBMIT' => 'Submit',
    'EMAIL_BODY_EXPLAIN' => 'Your message to the admins. All admins and mods may be able to read this.',
]);
