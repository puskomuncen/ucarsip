<?php

namespace PHPMaker2025\ucarsip;

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

/**
 * Push Notification class
 */
class PushNotification
{

    public function subscribe(): bool
    {
        return $this->addSubscription(Request()->getParsedBody());
    }

    public function send(): bool
    {
        global $TokenNameKey, $TokenValueKey;
        $payload = Request()->getParsedBody(); // Get all post data
        if ($TokenNameKey && isset($payload[$TokenNameKey])) { // Remove Token Name
            unset($payload[$TokenNameKey]);
        }
        if ($TokenValueKey && isset($payload[$TokenValueKey])) { // Remove Token Key
            unset($payload[$TokenValueKey]);
        }
        $tbl = Container(Config("SUBSCRIPTION_TABLE_VAR"));
        $filter = "";
        $keys = Param("key_m", []);
        if (count($keys) > 0) {
            $filter = QuotedName(Config("SUBSCRIPTION_FIELD_NAME_ID"), Config("SUBSCRIPTION_DBID")) . " IN (" . implode(", ", $keys) . ")";
        }
        $rows = $tbl->loadRecords($filter)->fetchAllAssociative();
        if (count($rows) == 0) {
            WriteJson([]);
            return false;
        }
        if (Config("SEND_PUSH_NOTIFICATION_TIME_LIMIT") >= 0) {
            @set_time_limit(Config("SEND_PUSH_NOTIFICATION_TIME_LIMIT")); // Set time limit for sending push notification
        }
        $subscriptions = [];
        foreach ($rows as $row) {
            if (count($row) > 0) {
                $subscriptions[] = Subscription::create([
                    "endpoint" => $row[Config("SUBSCRIPTION_FIELD_NAME_ENDPOINT")],
                    "publicKey" => $row[Config("SUBSCRIPTION_FIELD_NAME_PUBLIC_KEY")],
                    "authToken" => $row[Config("SUBSCRIPTION_FIELD_NAME_AUTH_TOKEN")],
                    "contentEncoding" => $row[Config("SUBSCRIPTION_FIELD_NAME_CONTENT_ENCODING")]
                ]);
            }
        }
        return $this->sendNotifications($subscriptions, $payload);
    }

    public function delete(): bool
    {
        return $this->deleteSubscription(Request()->getParsedBody());
    }

    protected function addSubscription(array $subscription): bool
    {
        $user = CurrentUserID() ?? CurrentUserName();
        $endpoint = $subscription["endpoint"] ?? "";
        $publicKey = $subscription["publicKey"] ?? "";
        $authToken = $subscription["authToken"] ?? "";
        $contentEncoding = $subscription["contentEncoding"] ?? "";
        if (
            IsEmpty(Config("SUBSCRIPTION_TABLE_VAR"))
            || IsEmpty(Config("SUBSCRIPTION_FIELD_NAME_USER"))
            || IsEmpty(Config("SUBSCRIPTION_FIELD_NAME_ENDPOINT"))
            || IsEmpty(Config("SUBSCRIPTION_FIELD_NAME_PUBLIC_KEY"))
            || IsEmpty(Config("SUBSCRIPTION_FIELD_NAME_AUTH_TOKEN"))
            || IsEmpty(Config("SUBSCRIPTION_FIELD_NAME_CONTENT_ENCODING"))
            || IsEmpty($endpoint)
            || IsEmpty($publicKey)
            || IsEmpty($authToken)
            || IsEmpty($contentEncoding)
        ) {
            return false;
        }
        $row = [
            Config("SUBSCRIPTION_FIELD_NAME_USER") => $user,
            Config("SUBSCRIPTION_FIELD_NAME_ENDPOINT") => $endpoint,
            Config("SUBSCRIPTION_FIELD_NAME_PUBLIC_KEY") => $publicKey,
            Config("SUBSCRIPTION_FIELD_NAME_AUTH_TOKEN") => $authToken,
            Config("SUBSCRIPTION_FIELD_NAME_CONTENT_ENCODING") => $contentEncoding
        ];

        // Insert subscription
        $addSubscription = false;
        $tbl = Container(Config("SUBSCRIPTION_TABLE_VAR"));
        if ($tbl && (!method_exists($tbl, "rowInserting") || $tbl->rowInserting(null, $row))) {
            $addSubscription = $tbl->insert($row);
            if ($addSubscription && method_exists($tbl, "rowInserted")) {
                $tbl->rowInserted(null, $row);
            }
        }
        WriteJson(["success" => $addSubscription]);
        return $addSubscription;
    }

    /**
     * Send Notifications
     *
     * @param array $subscriptions Array of Subscription
     * @param mixed $payload Payload, see https://developer.mozilla.org/en-US/docs/Mozilla/Add-ons/WebExtensions/API/notifications/NotificationOptions
     * @return bool
     */
    protected function sendNotifications(array $subscriptions, mixed $payload): bool
    {
        $auth = [
            "VAPID" => [
                "subject" => $payload["title"] ?? Language()->phrase("PushNotificationDefaultTitle"),
                "publicKey" => Config("PUSH_SERVER_PUBLIC_KEY"),
                "privateKey" => Config("PUSH_SERVER_PRIVATE_KEY"),
            ],
        ];
        $webPush = new WebPush($auth);
        $notifications = array_map(function ($subscription) use ($payload) {
            $options = $payload; // Clone
            return ["subscription" => $subscription, "payload" => $options];
        }, $subscriptions);

        // Send multiple notifications with payload
        foreach ($notifications as $notification) {
            $webPush->queueNotification(
                $notification["subscription"],
                json_encode($notification["payload"])
            );
        }

        // Check sent results
        $reports = [];
        foreach ($webPush->flush() as $report) { // $webPush->flush() returns Generator
            $reports[] = $report->jsonSerialize();
        }
        if (IsDebug()) {
            Log(json_encode($reports));
            $results = $reports;
        } else {
            $results = array_map(fn($report) => ["success" => $report["success"]], $reports); // Return "success" only
        }
        WriteJson($results);
        return in_array(true, array_column($reports, "success"), true); // Any success result
    }

    protected function deleteSubscription(array $subscription): bool
    {
        $user = CurrentUserID() ?? CurrentUserName();
        $endpoint = $subscription["endpoint"] ?? "";
        $publicKey = $subscription["publicKey"] ?? "";
        $authToken = $subscription["authToken"] ?? "";
        $contentEncoding = $subscription["contentEncoding"] ?? "";
        if (
            IsEmpty(Config("SUBSCRIPTION_TABLE_VAR"))
            || IsEmpty(Config("SUBSCRIPTION_FIELD_NAME_USER"))
            || IsEmpty(Config("SUBSCRIPTION_FIELD_NAME_ENDPOINT"))
            || IsEmpty(Config("SUBSCRIPTION_FIELD_NAME_PUBLIC_KEY"))
            || IsEmpty(Config("SUBSCRIPTION_FIELD_NAME_AUTH_TOKEN"))
            || IsEmpty(Config("SUBSCRIPTION_FIELD_NAME_CONTENT_ENCODING"))
        ) {
            WriteJson(["success" => false, "error" => "Invalid subscription table settings"]);
            return false;
        }
        if (
            IsEmpty($endpoint)
            || IsEmpty($publicKey)
            || IsEmpty($authToken)
            || IsEmpty($contentEncoding)
        ) {
            WriteJson(["success" => false, "error" => "Invalid subscription"]);
            return false;
        }
        $row = [
            Config("SUBSCRIPTION_FIELD_NAME_USER") => $user,
            Config("SUBSCRIPTION_FIELD_NAME_ENDPOINT") => $endpoint,
            Config("SUBSCRIPTION_FIELD_NAME_PUBLIC_KEY") => $publicKey,
            Config("SUBSCRIPTION_FIELD_NAME_AUTH_TOKEN") => $authToken,
            Config("SUBSCRIPTION_FIELD_NAME_CONTENT_ENCODING") => $contentEncoding
        ];
        // Delete subscription
        $deleteSubscription = true;
        $tbl = Container(Config("SUBSCRIPTION_TABLE_VAR"));
        $endpointField = $tbl->fields(Config("SUBSCRIPTION_FIELD_NAME_ENDPOINT"));
        $filter = $endpointField ? $endpointField->Expression . "=" . QuotedValue($endpoint, $endpointField->DataType, $tbl->Dbid) : "";
        if ($filter && (int)$tbl->getConnection()->fetchOne("SELECT COUNT(*) FROM " . Config("SUBSCRIPTION_TABLE") . " WHERE " . $filter) === 0) { // Subscription not exists
            WriteJson(["success" => true]);
            return true;
        }
        if (method_exists($tbl, "rowDeleting")) {
            $deleteSubscription = $tbl->rowDeleting($row);
        }
        if ($deleteSubscription) {
            if ($filter) {
                $deleteSubscription = $tbl->delete($row, $filter);
                if ($deleteSubscription && method_exists($tbl, "rowDeleted")) {
                    $tbl->rowDeleted($row);
                }
            } else {
                $deleteSubscription = false;
            }
        }
        WriteJson(["success" => $deleteSubscription]);
        return $deleteSubscription;
    }
}
