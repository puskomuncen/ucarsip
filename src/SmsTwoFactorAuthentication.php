<?php

namespace PHPMaker2025\ucarsip;

use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Recipient\Recipient;
use Exception;
use Throwable;

/**
 * Two Factor Authentication for SMS Authentication
 */
class SmsTwoFactorAuthentication extends AbstractTwoFactorAuthentication implements SendOneTimePasswordInterface
{
    public const TYPE = "sms";

    /**
     * Send one time password
     *
     * @param string $user User name
     * @param ?string $account User phone
     */
    public function sendOneTimePassword(string $user, #[\SensitiveParameter] ?string $account = null): bool|string
    {
        // Get phone number
        $phone = $this->checkAccount($account);
        if (IsEmpty($phone)) { // Check if empty, cannot use CheckPhone due to possible different phone number formats
            return sprintf($this->language->phrase("SendOtpSkipped"), $account, $user); // Return error message
        }

        // Create OTP and save in user profile
        $secret = $this->profile->getUserSecret("sms"); // Get user secret
        $code = Random(Config("TWO_FACTOR_AUTHENTICATION_PASS_CODE_LENGTH")); // Generate OTP
        $encryptedCode = Encrypt($code, $secret); // Encrypt OTP
        $this->profile->setOneTimePassword("sms", $phone, $encryptedCode);

        // Send OTP
        $obj = $this->load(Config("SMS_ONE_TIME_PASSWORD_TEMPLATE"), data: [
            "Code" => $code,
            "Account" => $user
        ]);
        $notification = new Notification($obj->Subject, ["sms"]);
        $recipient = new Recipient(phone: FormatPhoneNumber($phone));

        // Call Otp_Sending event
        if (Otp_Sending($notification, $recipient)) {
            try {
                Notifier()->send($notification, $recipient);
                return true; // Return success
            } catch (Throwable $e) {
                return $e->getMessage(); // Return error message
            }
        } else {
            return $this->language->phrase("SendOtpCancelled"); // User cancel
        }
    }

    /**
     * Check code
     *
     * @param string $otp One time password
     * @param string $code Code
     */
    public static function checkCode(string $otp, string $code): bool
    {
        return $otp == $code;
    }

    /**
     * Generate secret
     */
    public static function generateSecret(): string
    {
        return Random(); // Generate a radom number for secret, used for encrypting OTP
    }

    /**
     * Get user phone number
     *
     * @param string $user User
     * @return void
     */
    public function show(string $user): array
    {
        if ($this->isValidUser($user)) {
            $phone = $this->profile->getPhone(true); // Get verified phone number
            if (!IsEmpty($phone)) {
                return ["account" => $phone, "success" => true, "verified" => true];
            }
            $phone = $this->profile->getPhone(false); // Get unverified phone number
            return ["account" => $phone, "success" => true, "verified" => false];
        }
        return ["success" => false, "error" => "Missing user identifier"];
    }
}
