<?php

namespace PHPMaker2025\ucarsip;

/**
 * Reset User Secret Action
 */
class ResetUserSecretAction extends ListAction
{
    // Constructor
    public function __construct(
        public string $Action = "resetusersecret",
        public string $Caption = "",
        public bool $Allowed = true,
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
        $this->Caption = $this->language->phrase("ResetUserSecretBtn");
        $this->SuccessMessage = $this->language->phrase("ResetUserSecretSuccess");
        $this->FailureMessage = $this->language->phrase("ResetUserSecretFailure");
        $this->Allowed = IsAdmin();
    }

    // Set fields (override)
    public function setFields(DbFields $value): static
    {
        $this->reset();
        $this->fields = $value;
        $this->setVisible(UserProfile::create()->setUserName($this->fields[Config("LOGIN_USERNAME_FIELD_NAME")]->DbValue)
            ->load(HtmlDecode($this->fields[Config("USER_PROFILE_FIELD_NAME")]->DbValue))
            ->hasUserSecret(true));
        return $this;
    }

    // Handle the action
    public function handle(array $row, object $listPage): bool
    {
        if ($listPage->TableName == Config("USER_TABLE_NAME")) {
            $user = $row[Config("LOGIN_USERNAME_FIELD_NAME")];
            $result = UserProfile::create()
                ->setUserName($user)
                ->load(HtmlDecode($row[Config("USER_PROFILE_FIELD_NAME")] ?? ""))
                ->resetSecrets();
            if ($result) {
                WriteJson(["successMessage" => sprintf($this->SuccessMessage, $user), "disabled" => true]); // Disable the button
            } else {
                WriteJson(["failureMessage" => sprintf($this->FailureMessage, $user)]);
            }
            return $result;
        }
        return false;
    }
}
