<?php

namespace PHPMaker2025\ucarsip;

/**
 * Login Link Action
 */
class SendLoginLinkAction extends ListAction
{
    // Constructor
    public function __construct(
        public string $Action = "sendloginlink",
        public string $Caption = "",
        public bool $Allowed = true,
        public ?int $LifeTime = null,
        public ActionType $Method = ActionType::AJAX, // Postback (P) / Redirect (R) / Ajax (A)
        public ActionType $Select = ActionType::SINGLE, // Multiple (M) / Single (S) / Custom (C)
        public string|array $ConfirmMessage = "", // Message or Swal config
        public string $Icon = "fa-solid fa-star ew-icon", // Icon
        public string $Success = "", // JavaScript callback function name
        public mixed $Handler = null, // PHP callable to handle the action
        public string $SuccessMessage = "", // Default success message
        public string $FailureMessage = "", // Default failure message
    ) {
        $this->language = Language();
        $this->Caption = $this->language->phrase("SendLoginLinkBtn");
        $this->ConfirmMessage = [
            "text" => $this->language->phrase("EnterLoginLinkLifeTime"),
            "input" => "number",
            "inputValue" => $this->LifeTime ?? Config("LOGIN_LINK_LIFETIME"),
            "showCancelButton" => true
        ]; // Swal config
        $this->SuccessMessage = $this->language->phrase("SendLoginLinkSuccess");
        $this->FailureMessage = $this->language->phrase("SendLoginLinkFailed");
        $this->Allowed = IsAdmin();
    }

    // Handle the action
    public function handle(array $row, object $listPage): bool
    {
        if ($listPage->TableName == Config("USER_TABLE_NAME")) {
            $userName = $row[Config("LOGIN_USERNAME_FIELD_NAME")];
            $user = LoadUserByIdentifier($userName);
            $lifeTime = $listPage->ActionValue;
            if (!is_numeric($lifeTime)) {
                $lifeTime = Config("LOGIN_LINK_LIFETIME");
            }
            $link = CreateLoginLink($user, $lifeTime);
            $emailAddress = $user->get(Config("USER_EMAIL_FIELD_NAME"));
            if ($emailAddress != "") {
                // Load Email Content
                $email = new Email();
                $email->load(Config("EMAIL_LOGIN_LINK_TEMPLATE"), data: [
                    "From" => Config("SENDER_EMAIL"), // Replace Sender
                    "To" => $emailAddress, // Replace Recipient
                    "LoginLink" => $link->getUrl(),
                    "LifeTime" => $lifeTime
                ]);
                $args = ["user" => $user, "row" => $user->toArray()];
                $result = false;
                if (!method_exists($listPage, "emailSending") || $listPage->emailSending($email, $args)) {
                    $result = $email->send();
                }
                if ($result) {
                    WriteJson(["successMessage" => sprintf($this->SuccessMessage, $userName) . ". Link: " . $link->getUrl()]);
                } else {
                    WriteJson(["failureMessage" => sprintf($this->FailureMessage, $userName)]);
                }
                return $result;
            } else { // added by Masino Sinaga, October 30, 2024 in case email does not exist
				WriteJson(["successMessage" => "Email does not exist. Please copy this link: " . $link->getUrl()]);
				return true;
			}
        }
        return false;
    }
}
