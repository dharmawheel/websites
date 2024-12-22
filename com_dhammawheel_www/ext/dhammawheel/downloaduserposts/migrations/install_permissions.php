<?php
/**
 *
 * Download User Posts. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2024, Dhamma Wheel
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace dhammawheel\downloaduserposts\migrations;

class install_permissions extends \phpbb\db\migration\migration
{
    public static function depends_on()
    {
        return ['\phpbb\db\migration\data\v320\v320'];
    }

    /**
     * Add, update or delete data stored in the database during extension installation.
     *
     * https://area51.phpbb.com/docs/dev/3.2.x/migrations/data_changes.html
     *  permission.add: Add a new permission.
     *  permission.remove: Remove a permission.
     *  permission.role_add: Add a new permission role.
     *  permission.role_update: Update a permission role.
     *  permission.role_remove: Remove a permission role.
     *  permission.permission_set: Set a permission to Yes or Never.
     *  permission.permission_unset: Set a permission to No.
     *
     * @return array Array of data update instructions
     */
    public function update_data()
    {
        return [
            // Add new permissions
            ['permission.add', ['u_new_dhammawheel_downloaduserposts']], // New user permission

            // Set our new permissions
            ['permission.permission_set', ['ROLE_USER_FULL', 'u_new_dhammawheel_downloaduserposts']], // Give ROLE_USER_FULL u_new_dhammawheel_downloaduserposts permission
            ['permission.permission_set', ['ROLE_USER_STANDARD', 'u_new_dhammawheel_downloaduserposts']], // Give ROLE_USER_STANDARD u_new_dhammawheel_downloaduserposts permission
        ];
    }
}
