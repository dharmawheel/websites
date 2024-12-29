<?php
namespace dhammawheel\contactadminposts\acp;

class cap_acp_info {
    public function module() {
        return [
            'filename' => '\dhammawheel\contactadminposts\acp\cap_acp_module',
            'title' => 'ACP_CONTACTADMINPOSTS_TITLE',
            'modes' => [
                'contactadminposts' => [
                    'title' => 'ACP_CONTACTADMINPOSTS_TITLE',
                    'auth' => 'ext_dhammawheel/contactadminposts && acl_a_server',
                    'cat' => ['ACP_BOARD_CONFIGURATION'],
                ],
            ],
        ];
    }
}
