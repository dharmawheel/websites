<?php
namespace dhammawheel\contactadminposts\migrations;

use \phpbb\db\migration\migration;

class install_acp_module extends migration {
    public function effectively_installed(): bool {
        return $this->config->offsetExists('dhammawheel_contactadminposts_target_forum_id');
    }

    public static function depends_on(): array {
        return ['\phpbb\db\migration\data\v320\v320'];
    }

    public function update_data(): array {
        return [
            ['config.add', ['dhammawheel_contactadminposts_target_forum_id', 0]],
            ['module.add', ['acp', 'ACP_BOARD_CONFIGURATION', [
                'module_basename' => '\dhammawheel\contactadminposts\acp\cap_acp_module',
                'module_langname' => 'ACP_CONTACTADMINPOSTS_TITLE',
                'module_mode' => 'contactadminposts',
                'module_auth' => 'ext_dhammawheel/contactadminposts && acl_a_server',
            ]]],
        ];
    }

    public function revert_data(): array {
        return [
            ['config.remove', ['dhammawheel_contactadminposts_target_forum_id']],
            ['module.remove', ['acp', 'ACP_BOARD_CONFIGURATION', 'ACP_CONTACTADMINPOSTS_TITLE']],
        ];
    }
}
