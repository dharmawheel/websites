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
    'ACP_CONTACTADMINPOSTS_TITLE'	=> 'Contact Admin Posts Module',
    'ACP_CONTACTADMINPOSTS'			=> 'Contact Admin Posts Settings',
    'ACP_CONTACTADMINPOSTS_SETTINGS' => 'Settings for Contact Admin Posts',
    'ACP_CONTACTADMINPOSTS_EXT_SETTINGS' => 'Contact Admin Posts Settings',
    'ACP_CONTACTADMINPOSTS_SETTING_SAVED' => 'Saved settings for Contact Admin Posts',
    'LOG_ACP_CONTACTADMINPOSTS_SETTINGS'		=> '<strong>Contact Admin Posts settings updated</strong>',
]);
