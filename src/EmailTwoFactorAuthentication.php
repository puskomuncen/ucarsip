<?php

namespace PHPMaker2025\ucarsip;

use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Recipient\Recipient;
use Throwable;

/**
 * Two Factor Authentication for email Authentication
 */
class EmailTwoFactorAuthentication extends AbstractTwoFactorAuthentication implements SendOneTimePasswordInterface
{
    public const TYPE = "email";

    /**
     * Send one time password
     *
     * @param string $user User name
     * @param ?string $account User email
     */
    public function sendOneTimePassword(string $user, #[\SensitiveParameter] ?string $account = null): bool|string
    {
        // Get email address
        $email = $this->checkAccount($account);
        if (IsEmpty($email) || !CheckEmail($email)) { // Check if valid email address
            return sprintf($this->language->phrase("SendOtpSkipped"), $account, $user); // Return error message
        }

        // Create OTP and save in user profile
        $secret = $this->profile->getUserSecret("email"); // Get user secret
        $code = Random(Config("TWO_FACTOR_AUTHENTICATION_PASS_CODE_LENGTH")); // Generate OTP
        $encryptedCode = Encrypt($code, $secret); // Encrypt OTP
        $this->profile->setOneTimePassword("email", $email, $encryptedCode);

        // Send OTP email
        $obj = $this->load(Config("EMAIL_ONE_TIME_PASSWORD_TEMPLATE"), data: [
            "Code" => $code,
            "Account" => $user
        ]);
        $notification = (new Notification($obj->Subject, ["email"]))->content($obj->Content);
        $recipient = new Recipient(email: $email);

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
     * Get user email address
     *
     * @param string $user User name
     * @return void
     */
    public function show(string $user): array
    {
        if ($this->isValidUser($user)) {
            $email = $this->profile->getEmail(true); // Get verified email
            if (!IsEmpty($email)) {
                return ["account" => $email, "success" => true, "verified" => true];
            }
            $email = $this->profile->getEmail(false); // Get unverified email
            return ["account" => $email, "success" => true, "verified" => false];
        }
        return ["success" => false, "error" => "Missing user identifier"];
    }
}
