<?php
/**
 * @author Viktar Dubiniuk <dubiniuk@owncloud.com>
 *
 * @copyright Copyright (c) 2019, ownCloud GmbH
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\Files_Versions;

use OC\Files\Filesystem;
use OC\Files\View;

class FileHelper {
	public const VERSIONS_RELATIVE_PATH = '/files_versions';

	/**
	 * Create recursively missing directories inside of files_versions
	 * that match the given path to a file.
	 *
	 * @param View $view view on data/user/
	 * @param string $path to a file, relative to the user's "files" folder
	 *
	 * @return void
	 */
	public function createMissingDirectories(View $view, $path) {
		$dirName = Filesystem::normalizePath(\dirname($path));
		$dirParts = \explode('/', $dirName);
		$dir = self::VERSIONS_RELATIVE_PATH;
		foreach ($dirParts as $part) {
			$dir = \rtrim($dir, '/') . '/' . $part;
			if (!$view->file_exists($dir)) {
				$view->mkdir($dir);
			}
		}
	}

	/**
	 * Get current size of all versions for a given user
	 *
	 * @param string $uid user who owns the versions
	 *
	 * @return int
	 */
	public function getVersionsSize($uid) {
		$view = $this->getUserView($uid);
		$fileInfo = $view->getFileInfo(self::VERSIONS_RELATIVE_PATH);
		return isset($fileInfo['size']) ? $fileInfo['size'] : 0;
	}

	/**
	 * Split /path/filename.ext.v1234567
	 * into [ 'path' => '/path/filename.ext', 'revision' => '1234567' ]
	 *
	 * @param string $versionPath
	 *
	 * @return array
	 */
	public function getPathAndRevision($versionPath) {
		\preg_match_all(
			'#(?<path>.*)\.v(?<timestamp>\d+)$#',
			$versionPath,
			$versionInfo
		);
		return [
			'path' => $versionInfo['path'][0],
			'revision' => $versionInfo['timestamp'][0]
		];
	}

	/**
	 * @param string $uid
	 *
	 * @return View
	 *
	 * @throws \Exception
	 */
	public function getUserView($uid) {
		return new View("/$uid");
	}

	/**
	 * @param string $uid
	 *
	 * @return View
	 *
	 * @throws \Exception
	 */
	public function getFilesView($uid) {
		return new View("/$uid/files");
	}

	/**
	 * @param string $uid
	 *
	 * @return View
	 *
	 * @throws \Exception
	 */
	public function getVersionsView($uid) {
		return new View("/$uid" . self::VERSIONS_RELATIVE_PATH);
	}
}
