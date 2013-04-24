<?php
/* zKillboard
 * Copyright (C) 2012-2013 EVE-KILL Team and EVSCO.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once( dirname(__FILE__) . "/../init.php" );

$page = 1;

while(true)
{
	$incJson = file_get_contents("http://zkillboard.com/api/api-only/page/".urlencode($page)."/corporationID/".urlencode($boardCorpId));

	$data = json_decode($incJson);

	$insertCount = 0;

	foreach($data as $kill)
	{
		unset($kill->_stringValue);

		$hash = Util::getKillHash(null, $kill);
		$json = json_encode($kill);
		$killID = $kill->killID;
		$source = "zKB API Fetch";

		$insertCount += Db::execute("insert ignore into zz_killmails (killID, hash, source, kill_json) values (:killid, :hash, :source, :json)", array(":killid" => $killID, ":hash" => $hash, ":source" => $source, ":json" => $json));
	}

	if($insertCount <= 0)
		break;

	echo "Inserted $insertCount new kills from zKB api...\n";
	$page += 1;
}

echo "Done\n";
