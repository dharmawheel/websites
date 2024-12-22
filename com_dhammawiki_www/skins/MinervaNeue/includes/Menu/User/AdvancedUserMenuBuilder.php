<?php
/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 */
namespace MediaWiki\Minerva\Menu\User;

use MediaWiki\Minerva\Menu\Definitions;
use MediaWiki\Minerva\Menu\Entries\ProfileMenuEntry;
use MediaWiki\Minerva\Menu\Entries\SingleMenuEntry;
use MediaWiki\Minerva\Menu\Group;
use MediaWiki\User\User;
use MessageLocalizer;

/**
 * Logged-in, advanced Mobile Contributions user menu config generator.
 */
final class AdvancedUserMenuBuilder implements IUserMenuBuilder {
	private MessageLocalizer $messageLocalizer;
	private User $user;
	private Definitions $definitions;

	/**
	 * @param MessageLocalizer $messageLocalizer
	 * @param User $user
	 * @param Definitions $definitions A menu items definitions set
	 */
	public function __construct(
		MessageLocalizer $messageLocalizer, User $user, Definitions $definitions
	) {
		$this->messageLocalizer = $messageLocalizer;
		$this->user = $user;
		$this->definitions = $definitions;
	}

	/**
	 * @inheritDoc
	 * @param array $personalTools list of personal tools generated by
	 * SkinTemplate::getPersonalTools
	 * @return Group
	 */
	public function getGroup( array $personalTools ): Group {
		$group = new Group( 'p-personal' );
		$trackingKeyOverrides = [
			'mytalk' => 'userTalk',
			'watchlist' => 'unStar',
			'mycontris' => 'contributions',
		];

		foreach ( $personalTools as $key => $item ) {
			if ( in_array( $key, [ 'preferences', 'betafeatures', 'uploads' ] ) ) {
				continue;
			}
			// Special casing for userpage to support Extension:GrowthExperiments.
			// This can be removed when T291568 is resolved.
			if ( $key === 'userpage' ) {
				$entry = new ProfileMenuEntry( $this->user );
				$entry->overrideProfileURL(
					$item['href'],
					$item['text']
				);
				$group->insertEntry( $entry );
				continue;
			}
			$icon = $item['icon'] ?? null;
			if ( $icon ) {
				$entry = SingleMenuEntry::create(
					$key,
					$item['text'],
					$item['href'],
					$item['class'] ?? '',
					$icon
				);
				// override tracking key where key mismatch
				if ( array_key_exists( $key, $trackingKeyOverrides ) ) {
					$entry->trackClicks( $trackingKeyOverrides[ $key ] );
				}
				$group->insertEntry( $entry );
			}
		}
		return $group;
	}
}