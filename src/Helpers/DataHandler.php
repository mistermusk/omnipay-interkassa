<?php

namespace Omnipay\InterKassa\Helpers;


class DataHandler {

    public static $statusesByCodes;
    public static $statusesByNames;
    public static $statuses;

    static function getStatuses() {
        if(self::$statuses) { return self::$statuses; }
        $statuses = [
            (object) [
                "code" => 1,
                "name" => "wait_accept",
                "description" => "Pending moderation check",
                "finality" => "No"
            ],
            (object) [
                "code" => 2,
                "name" => "invoke",
                "description" => "Approved by moderation",
                "finality" => "No"
            ],
            (object) [
                "code" => 4,
                "name" => "hold",
                "description" => "Withdrawal is temporarily frozen",
                "finality" => "No"
            ],
            (object) [
                "code" => 5,
                "name" => "unhold",
                "description" => "Withdrawal has been unfrozen",
                "finality" => "No"
            ],
            (object) [
                "code" => 6,
                "name" => "process",
                "description" => "Payment system is processing the withdrawal",
                "finality" => "No"
            ],
            (object) [
                "code" => 7,
                "name" => "enroll",
                "description" => "Funds have been credited",
                "finality" => "No"
            ],
            (object) [
                "code" => 8,
                "name" => "success",
                "description" => "Withdrawal successfully completed",
                "finality" => "Yes"
            ],
            (object) [
                "code" => 9,
                "name" => "canceled",
                "description" => "Withdrawal request was canceled",
                "finality" => "Yes"
            ],
            (object) [
                "code" => 10,
                "name" => "chargeback",
                "description" => "Funds awaiting to be returned",
                "finality" => "Yes"
            ],
            (object) [
                "code" => 11,
                "name" => "chargebacked",
                "description" => "Funds have been returned to the wallet",
                "finality" => "Yes"
            ],
            (object) [ 
                "code" => 12,
                "name" => "check",
                "description" => "Withdrawal has been initiated in the payment system but hasn't been processed yet",
                "finality" => "No"
            ]
        ];
        $statusesByCodes = new \stdClass;
        $statusesByNames = new \stdClass;
        foreach($statuses as $k => $v) {
            $statusesByNames->{$v->name} = $v;
            $statusesByCodes->{$v->code} = $v;
        }
        self::$statusesByNames = $statusesByNames;
        self::$statusesByCodes = $statusesByCodes;
        self::$statuses = $statuses;
        return $statuses;
    }

    static function convertRawState($rawStatus) {
        $rawStatus = (int)$rawStatus;

        if(!$rawStatus) {
            return 'failed';
        }
        if (in_array($rawStatus, [9, 10, 11])) {
            return 'failed';
        }
        if(in_array($rawStatus, [8])) {
            return 'success';
        }
        return 'pending';
    }
}