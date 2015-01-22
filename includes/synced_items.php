<?php

require_once(dirname(__FILE__) . '/../config/config.php');

date_default_timezone_set(@date_default_timezone_get());

function getPMSUrl() {
    global $plexWatch;
    if ($plexWatch['https'] == 'yes') {
        $plexWatchPmsUrl = "https://".$plexWatch['pmsIp'].":".$plexWatch['pmsHttpsPort']."";
    }else if ($plexWatch['https'] == 'no') {
        $plexWatchPmsUrl = "http://".$plexWatch['pmsIp'].":".$plexWatch['pmsHttpPort']."";
    }

    return $plexWatchPmsUrl;
}

function getPlexTVURL() {
    $plexTvApi = "https://plex.tv";
    return $plexTvApi;
}

function getPlexAuthToken() {
    global $plexWatch;
    if (!empty($plexWatch['myPlexAuthToken'])) {
        return $plexWatch['myPlexAuthToken'];
    } else {
        error_log('PlexWatchWeb error: unable to get Plex token.');
        return false;
    }
}

function getFriendsList() {

    $fileContents = '';

    if (getPlexAuthToken()) {
        if ($fileContents = file_get_contents("".getPlexTVURL()."/api/users?X-Plex-Token=".getPlexAuthToken()."")) {
            $userList = simplexml_load_string($fileContents);
        } else {
            error_log('PlexWatchWeb error: unable to retrieve friends list.');
            return false;
        }
    } else {
        error_log('PlexWatchWeb error: unable to get Plex token.');
        return false;
    }

    $i = 0;

    foreach ($userList as $users) {
        $userDetails[$i]['id'] = (string)$users['id'];
        $userDetails[$i]['title'] = (string)$users['title'];

        $i++;
    }

    return $userDetails;
}

function getLocalUser($item) {

    $fileContents = '';
    if (getPlexAuthToken()) {
        if ($fileContents = file_get_contents("" . getPlexTVURL() . "/users/account?X-Plex-Token=" . getPlexAuthToken() . "")) {
            $localUserInfo = simplexml_load_string($fileContents);
        } else {
            error_log('PlexWatchWeb error: unable to retrieve local user details.');
            return false;
        }
    } else {
        error_log('PlexWatchWeb error: unable to get Plex token.');
        return false;
    }

    $localUser['id'] = (string)$localUserInfo['id'];
    $localUser['name'] = (string)$localUserInfo->username;
    if ($item === 'name') {
        return $localUser['name'];
    } else {
        return $localUser['id'];
    }

}

function getServerList() {

    $fileContents = '';
    if (getPlexAuthToken()) {
        if ($fileContents = file_get_contents("" . getPlexTVURL() . "/pms/servers.xml?includeLite=1&X-Plex-Token=" . getPlexAuthToken() . "")) {
            $serverList = simplexml_load_string($fileContents);
        } else {
            error_log('PlexWatchWeb error: unable to retrieve server list.');
            return false;
        }
    } else {
        error_log('PlexWatchWeb error: unable to get Plex token.');
        return false;
    }

    $i = 0;

    foreach ($serverList as $serverItem) {
        if ((string)$serverItem['name'] !== 'Cloud Sync') {
            $server[$i]['id'] = (string)$serverItem['machineIdentifier'];
            $server[$i]['name'] = (string)$serverItem['name'];
            $i++;
        }
    }

    return $server;
}

function getSyncItems()
{
    $localUserName = getLocalUser('name');
    $friends = getFriendsList();
    $localUserId = getLocalUser('id');
    $serverList = getServerList();

    if (!$localUserName || !$localUserId || !$friends || !$serverList) {
        return false;
    }

    $i = 0;
    foreach ($serverList as $machine) {
        $fileContents = '';
        if ($fileContents = file_get_contents("" . getPlexTVURL() . "/servers/" . $machine['id'] . "/sync_lists?X-Plex-Token=" . getPlexAuthToken() . "")) {
            $syncList = simplexml_load_string($fileContents);
        } else {
            error_log('PlexWatchWeb error: unable to retrieve sync list.');
            return false;
        }

        foreach ($syncList->SyncList as $synced) {
            $firstArray['server_name'] = $machine['name'];
            $firstArray['device_name'] = $synced->Device['name'];
            $firstArray['device_product'] = $synced->Device['product'];
            $firstArray['device_platform'] = $synced->Device['platform'];
            $firstArray['device'] = $synced->Device['device'];
            $firstArray['user_id'] = $synced->Device['userID'];
            foreach ($synced->SyncItems->SyncItem as $syncedItem) {
                $syncItemArray[$i]['server_name'] = (string)$firstArray['server_name'];
                $syncItemArray[$i]['device_name'] = (string)$firstArray['device_name'];
                $syncItemArray[$i]['device_product'] = (string)$firstArray['device_product'];
                $syncItemArray[$i]['device_platform'] = (string)$firstArray['device_platform'];
                $syncItemArray[$i]['device'] = (string)$firstArray['device'];
                $syncItemArray[$i]['user_id'] = (string)$firstArray['user_id'];
                $syncItemArray[$i]['user_name'] = '';

                foreach ($friends as $userItem) {
                    if ($syncItemArray[$i]['user_id'] === $userItem['id']) {
                        $syncItemArray[$i]['user_name'] = FriendlyName($userItem['title'], $syncItemArray[$i]['device_platform']);
                    }
                }

                if ($syncItemArray[$i]['user_id'] === $localUserId) {
                    $syncItemArray[$i]['user_name'] = FriendlyName($localUserName, $syncItemArray[$i]['device_platform']);
                }

                $syncItemArray[$i]['item_id'] = (string)$syncedItem['id'];
                $syncItemArray[$i]['item_root_title'] = (string)$syncedItem['rootTitle'];
                $syncItemArray[$i]['item_title'] = (string)$syncedItem['title'];
                $syncItemArray[$i]['item_type'] = (string)$syncedItem['metadataType'];
                $syncItemArray[$i]['item_state'] = (string)$syncedItem->Status['state'];
                $syncItemArray[$i]['item_count'] = (string)$syncedItem->Status['itemsCount'];
                $syncItemArray[$i]['item_complete_count'] = (string)$syncedItem->Status['itemsCompleteCount'];
                $syncItemArray[$i]['item_total_size'] = (string)$syncedItem->Status['totalSize'];
                $syncItemArray[$i]['item_downloaded_count'] = (string)$syncedItem->Status['itemsDownloadedCount'];

                $syncItemArray[$i]['item_downloaded_percent'] =
                    round(((int)$syncedItem->Status['itemsDownloadedCount'] / (int)$syncedItem->Status['itemsCompleteCount']) * 100);

                $syncItemArray[$i]['item_music_bitrate'] = (string)$syncedItem->MediaSettings['musicBitrate'];
                $syncItemArray[$i]['item_video_quality'] = (string)$syncedItem->MediaSettings['videoQuality'];
                $syncItemArray[$i]['item_video_resolution'] = (string)$syncedItem->MediaSettings['videoResolution'];

                $i++;
            }

        }
    }

    return $syncItemArray;
}

$syncedItems = getSyncItems();

if ($syncedItems) {
    $output_array = array("status" => "success", "data" => $syncedItems);
} else {
    $output_array = array("status" => "error", "data" => null);
}

echo json_encode($output_array);

?>
