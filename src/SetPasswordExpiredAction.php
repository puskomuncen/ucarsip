<?php

namespace PHPMaker2025\ucarsip;

/**
 * Set Password Expired Action
 */
class SetPasswordExpiredAction extends ListAction
{
    // Constructor
    public function __construct(
        public string $Action = "setpasswordexpired",
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
        $this->Caption = $this->language->phrase("SetPasswordExpiredBtn");
        $this->SuccessMessage = $this->language->phrase("SetPasswordExpiredSuccess");
        $this->FailureMessage = $this->language->phrase("SetPasswordExpiredFailure");
        $this->Allowed = IsAdmin();
    }

    // Handle the action
    public function handle(array $row, object $listPage): bool
    {
        if ($listPage->TableName == Config("USER_TABLE_NAME")) {
            return UserProfile::create()
                ->setUserName($row[Config("LOGIN_USERNAME_FIELD_NAME")])
                ->load(HtmlDecode($row[Config("USER_PROFILE_FIELD_NAME")] ?? ""))
                ->setPasswordExpired();
        }
        return false;
    }
}
