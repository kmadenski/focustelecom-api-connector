<?php
/**
 * Created by PhpStorm.
 * User: kmadenski
 * Date: 17.07.19
 * Time: 13:07
 */

namespace FocusConnector\Core;


class ActivityFactory
{
    public static function loggedInStart(): \stdClass
    {
        $activity = new \stdClass();
        $activity->activity = "loggedIn";
        $activity->action = "start";
        return $activity;
    }

    public static function campaignStart($campaignsId): \stdClass
    {
        $activity = new \stdClass();
        $activity->activity = "campaign";
        $activity->action = "start";
        $activity->campaigns_id = $campaignsId;
        return $activity;
    }
}
