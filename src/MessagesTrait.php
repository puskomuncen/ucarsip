<?php

namespace PHPMaker2025\ucarsip;

use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
trait MessagesTrait
{
    // Flash bag
    private ?FlashBagInterface $flashBag = null;

    // Use JavaScript message
    public ?bool $UseJavaScriptMessage = null;

    // Get flash bag
    public function getFlashBag(): FlashBagInterface
    {
        return $this->flashBag ??= FlashBag();
    }

    // Peek message
    public function peekMessage(): array
    {
        return $this->getFlashBag()->peek("message");
    }

    // Get message
    public function getMessage(): string
    {
        return implode("<br>", $this->getFlashBag()->get("message") ?? []);
    }

    // Set message
    public function setMessage(string|array $msg): void
    {
        $this->getFlashBag()->set("message", $msg);
    }

    // Add message
    public function addMessage(mixed $msg): void
    {
        $this->getFlashBag()->add("message", $msg);
    }

    // Peek failure message
    public function peekFailureMessage(): array
    {
        return $this->getFlashBag()->peek("failure");
    }

    // Get failure message
    public function getFailureMessage(): string
    {
        return implode("<br>", $this->getFlashBag()->get("failure") ?? []);
    }

    // Set failure message
    public function setFailureMessage(string|array $msg): void
    {
        $this->getFlashBag()->set("failure", $msg);
    }

    // Add failure message
    public function addFailureMessage(mixed $msg): void
    {
        $this->getFlashBag()->add("failure", $msg);
    }

    // Peek success message
    public function peekSuccessMessage(): array
    {
        return $this->getFlashBag()->peek("success");
    }

    // Get success message
    public function getSuccessMessage(): string
    {
        return implode("<br>", $this->getFlashBag()->get("success") ?? []);
    }

    // Set success message
    public function setSuccessMessage(string|array $msg): void
    {
        $this->getFlashBag()->set("success", $msg);
    }

    // Add success message
    public function addSuccessMessage(mixed $msg): void
    {
        $this->getFlashBag()->add("success", $msg);
    }

    // Peek warning message
    public function peekWarningMessage(): array
    {
        return $this->getFlashBag()->peek("warning");
    }

    // Get warning message
    public function getWarningMessage(): string
    {
        return implode("<br>", $this->getFlashBag()->get("warning") ?? []);
    }

    // Set warning message
    // Set warning message
    public function setWarningMessage(string|array $msg): void
    {
        $this->getFlashBag()->set("warning", $msg);
    }

    // Add warning message
    public function addWarningMessage(mixed $msg): void
    {
        $this->getFlashBag()->add("warning", $msg);
    }

    // Peek message heading
    public function peekMessageHeading(): array
    {
        return $this->getFlashBag()->peek("heading");
    }

    // Get message heading
    public function getMessageHeading(): string
    {
        return implode("<br>", $this->getFlashBag()->get("heading") ?? []);
    }

    // Set message heading
    public function setMessageHeading(mixed $msg): void
    {
        $this->getFlashBag()->set("heading", $msg);
    }

    // Clear all messages
    public function clearMessages(): mixed
    {
        return $this->getFlashBag()->clear();
    }

    // Show message
    public function showMessage(): void
    {
        $hidden = $this->UseJavaScriptMessage ?? Config("USE_JAVASCRIPT_MESSAGE");
		$msg_box = Config("MS_USE_MESSAGE_BOX_INSTEAD_OF_TOAST"); // added by Masino Sinaga, October 13, 2024
        $html = "";
        // Message heading, Modified by Masino Sinaga, February 20, 2025
        if ($msg_box == false) {
			$heading = fn() => ($h = $this->getMessageHeading()) ? '<h5 class="alert-heading">' . $h . '</h5>' : '';
		} else {
			$heading = fn() => ($h = $this->getMessageHeading()) ? '' : '';
		}
        // Message showing
        $messageShowing = function($msg, $type = "") {
            $message = $msg;
            if (method_exists($this, "messageShowing")) {
                $this->messageShowing($message, $type);
            };
            return $message;
        };
        // Message
        if ($message = $messageShowing($this->getMessage())) {
			if ($msg_box == false) { // Begin added by Masino Sinaga, October 13, 2024
				$html .= '<div class="alert alert-info alert-dismissible ew-info">' . $heading() . '<i class="icon fa-solid fa-info"></i>' . $message . '</div>';
			} else {
				$html .= '<div class="alert alert-info alert-dismissible ew-info">' . $heading() . '<div class="row"><div class="col-2"><span style="font-size: 3rem;"><i class="icon fa-solid fa-circle-info" style="vertical-align: text-top;"></i></span></div><div class="col-10">' . $message . '</div></div></div>';
			} // End added by Masino Sinaga, October 13, 2024
        }
        // Warning message
        if ($warningMessage = $messageShowing($this->getWarningMessage(), "warning")) {
			if ($msg_box == false) { // Begin added by Masino Sinaga, October 13, 2024
				$html .= '<div class="alert alert-warning alert-dismissible ew-warning">' . $heading() . '<i class="icon fa-solid fa-exclamation"></i>' . $warningMessage . '</div>';
			} else {
				$html .= '<div class="alert alert-warning alert-dismissible ew-warning">' . $heading() . '<div class="row"><div class="col-2"><span style="font-size: 3rem;"><i class="icon fa-solid fa-circle-exclamation" style="vertical-align: text-top;"></i></span></div><div class="col-10">' . $warningMessage . '</div></div></div>';
			} // End added by Masino Sinaga, October 13, 2024
        }
        // Success message
        if ($successMessage = $messageShowing($this->getSuccessMessage(), "success")) {
            if ($msg_box == false) { // Begin added by Masino Sinaga, October 13, 2024
				$html .= '<div class="alert alert-success alert-dismissible ew-success">' . $heading() . '<i class="icon fa-solid fa-check"></i>' . $successMessage . '</div>';
			} else {
				$html .= '<div class="alert alert-success alert-dismissible ew-success">' . $heading() . '<div class="row"><div class="col-2"><span style="font-size: 3rem;"><i class="icon fa-solid fa-circle-check" style="vertical-align: text-top;"></i></span></div><div class="col-10">' . $successMessage . '</div></div></div>';
			} // End added by Masino Sinaga, October 13, 2024
        }
        // Failure message
        if ($errorMessage = $messageShowing($this->getFailureMessage(), "failure")) {
			if ($msg_box == false) { // Begin added by Masino Sinaga, October 13, 2024
				$html .= '<div class="alert alert-danger alert-dismissible ew-error">' . $heading() . '<i class="icon fa-solid fa-ban"></i>' . $errorMessage . '</div>';
			} else {
				$html .= '<div class="alert alert-danger alert-dismissible ew-error">' . $heading() . '<div class="row"><div class="col-2"><span style="font-size: 3rem;"><i class="icon fa-solid fa-circle-xmark" style="vertical-align: text-top;"></i></span></div><div class="col-10">' . $errorMessage . '</div></div></div>';
			} // End added by Masino Sinaga, October 13, 2024
        }
        if ($html && !$hidden) {
            $html = '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="' . Language()->phrase("CloseBtn") . '"></button>' . $html;
        }
        echo '<div class="ew-message-dialog' . ($hidden ? ' d-none' : '') . '">' . $html . '</div>';
    }

    // Get message as array
    public function getMessages(): array
    {
        $messages = [];
        // Message heading
        if ($heading = $this->getMessageHeading()) {
            $messages["heading"] = $heading;
        }
        // Message
        if ($message = $this->getMessage()) {
            $messages["message"] = $message;
        }
        // Warning message
        if ($warningMessage = $this->getWarningMessage()) {
            $messages["warningMessage"] = $warningMessage;
        }
        // Success message
        if ($successMessage = $this->getSuccessMessage()) {
            $messages["success"] = true;
            $messages["successMessage"] = $successMessage;
        }
        // Failure message
        if ($failureMessage = $this->getFailureMessage()) {
            $messages["failureMessage"] = $failureMessage;
        }
        return $messages;
    }
}
