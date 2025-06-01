<?php

namespace PHPMaker2025\ucarsip;

use Spatie\Color\Hex;
use Spatie\Color\Rgb;
use Spatie\Color\Hsl;
use Exception;

/**
 * CAPTCHA class
 */
class PhpCaptcha extends CaptchaBase
{
    public static string $BackgroundColor = "FFFFFF"; // Hex string
    public static string $TextColor = "003359"; // Hex string
    public static string $NoiseColor = "64A0C8"; // Hex string
    public static string $DarkTextColor = ""; // Hex string
    public static string $DarkBackgroundColor = "212529"; // Hex string
    public static string $DarkNoiseColor = ""; // Hex string
    public static int $DefaultAdjust = 30;
    public static int $Width = 250;
    public static int $Height = 50;
    public static int $Characters = 6;
    public static int $FontSize = 0;
    public static string $Font = "monofont";
    public string $Response = "";
    public string $ResponseField = "captcha";

    /**
     * Constructor
     */
    public function __construct(
        protected Language $language
    ) {
        if (self::$FontSize <= 0) {
            self::$FontSize = round($this->getHeight() * 0.55);
        }
    }

    /**
     * Generate code
     *
     * @param int $Characters Number of characters
     * @return string
     */
    protected function generateCode(int $Characters): string
    {
        $possible = "23456789BCDFGHJKMNPQRSTVWXYZ"; // Possible characters
        $code = "";
        $i = 0;
        while ($i < $Characters) {
            $code .= substr($possible, mt_rand(0, strlen($possible) - 1), 1);
            $i++;
        }
        return $code;
    }

    /**
     * Convert hex to RGB
     *
     * @param string $hexstr Hex string
     * @return Rgba Color
     */
    protected function hexToRgb(string $hexstr): Rgb
    {
        if (!str_starts_with($hexstr, "#")) {
            $hexstr = "#" . $hexstr;
        }
        return Hex::fromString($hexstr)->toRgb();
    }

    /**
     * Adjust lightness (Darken/Lighten) a HSL value
     *
     * @param Hsl $hsl Hsl value
     * @param ?int $amount
     * @return Hsl
     */
    private function adjustLightness(Hsl $hsl, ?int $amount = null): Hsl
    {
        $amount ??= self::$DefaultAdjust;
        $lightness = $hsl->lightness();
        $lightness = $lightness + $amount;
        $lightness = ($lightness < 0) ? 0 : $lightness;
        $lightness = ($lightness > 100) ? 100 : $lightness;
        return new Hsl($hsl->hue(), $hsl->saturation(), $lightness);
    }

    /**
     * Output image
     *
     * @return string Code
     */
    public function show(): string
    {
        $code = $this->generateCode(self::$Characters);
        $originalCode = $code;
        $code = "";
        $len = strlen($originalCode);
        for ($i = 0; $i < $len; $i++) {
            $code .= $originalCode[$i];
            if ($i < $len - 1) {
                $code .= " ";
            }
        }
        $code = trim($code);
        $width = $this->getWidth();
        $height = $this->getHeight();
        try {
            $image = imagecreatetruecolor($width, $height * 2);
        } catch (Exception $e) {
            throw new Exception("PhpCaptcha: Cannot initialize new GD image stream - " . $e->getMessage());
        }
        $rgb = $this->hexToRgb(self::$BackgroundColor);
        $backgroundColor = imagecolorallocate($image, $rgb->red(), $rgb->green(), $rgb->blue());
        imagefill($image, 0, 0, $backgroundColor);
        $rgb = $this->hexToRgb(self::$DarkBackgroundColor);
        $backgroundColor = imagecolorallocate($image, $rgb->red(), $rgb->green(), $rgb->blue());
        imagefilledrectangle($image, 0, $height, $width, $height * 2, $backgroundColor);
        $rgb = $this->hexToRgb(self::$TextColor);
        $textColor = imagecolorallocate($image, $rgb->red(), $rgb->green(), $rgb->blue());
        $rgb = self::$DarkTextColor
            ? $this->hexToRgb(self::$DarkTextColor)
            : $this->adjustLightness($rgb->toHsl(), self::$DefaultAdjust)->toRgb(); // Increase lightness for dark mode
        $darkTextColor = imagecolorallocate($image, $rgb->red(), $rgb->green(), $rgb->blue());
        $rgb = $this->hexToRgb(self::$NoiseColor);
        $noiseColor = imagecolorallocate($image, $rgb->red(), $rgb->green(), $rgb->blue());
        $rgb = self::$DarkNoiseColor
            ? $this->hexToRgb(self::$DarkNoiseColor)
            : $this->adjustLightness($rgb->toHsl(), self::$DefaultAdjust * -1)->toRgb(); // Decrease lightness for dark mode
        $darkNoiseColor = imagecolorallocate($image, $rgb->red(), $rgb->green(), $rgb->blue());
        // Generate random dots in background
        for ($i = 0; $i < ($width * $height) / 3; $i++) {
            $centerX = mt_rand(0, $width);
            $centerY = mt_rand(0, $height);
            imagefilledellipse($image, $centerX, $centerY, 1, 1, $noiseColor);
            imagefilledellipse($image, $centerX, $centerY + $height, 1, 1, $darkNoiseColor);
        }
        // Generate random lines in background
        for ($i = 0; $i < ($width * $height) / 150; $i++) {
            $x1 = mt_rand(0, $width);
            $y1 = mt_rand(0, $height);
            $x2 = mt_rand(0, $width);
            $y2 = mt_rand(0, $height);
            imageline($image, $x1, $y1, $x2, $y2, $noiseColor);
            imageline($image, $x1, $y1 + $height, $x2, $y2 + $height, $darkNoiseColor);
        }
        $fontFile = self::$Font;
        // Always use full path
        if (!ContainsString($fontFile, ".")) {
            $fontFile .= ".ttf";
        }
        $fontFile = IncludeTrailingDelimiter(Config("FONT_PATH"), true) . $fontFile;
        // Create textbox and add text
        try {
            $textBox = imagettfbbox(self::$FontSize, 0, $fontFile, $code);
        } catch (Exception $e) {
            throw new Exception("PhpCaptcha: Error in imagettfbbox function - " . $e->getMessage());
        }
        $x = ($width - $textBox[4]) / 2;
        $y = ($height - ($textBox[5] - $textBox[3])) / 2;
        try {
            imagettftext($image, self::$FontSize, 0, intval($x), intval($y), $textColor, $fontFile, $code);
            imagettftext($image, self::$FontSize, 0, intval($x), intval($y + $height), $darkTextColor, $fontFile, $code);
        } catch (Exception $e) {
            throw new Exception("PhpCaptcha: Error in imagettfbbox function - " . $e->getMessage());
        }
        // Output captcha image to browser
        if (ob_get_length()) { // Clean buffer
            ob_end_clean();
        }
        ob_start();
        AddHeader("Content-Type", "image/png");
        imagepng($image);
        $data = ob_get_contents();
        ob_end_clean();
        Write($data);
        imagedestroy($image);
        return $originalCode;
    }

    // Width
    public function getWidth(): int
    {
        return self::$Width;
    }

    // Height
    public function getHeight(): int
    {
        return self::$Height;
    }

    // HTML tag
    public function getHtml(): string
    {
        global $Page;
		// Begin of modification by Masino Sinaga, September 17, 2023
		if (CurrentPageID() == "add" || CurrentPageID() == "edit" || CurrentPageID() == "register") {
			$classAttr = ($Page->OffsetColumnClass) ? ' class="' . $Page->OffsetColumnClass . '"' : "";
		} else {
			$classAttr = ' class="col-sm-12"';
		}
		// End of modification by Masino Sinaga, September 17, 2023
        $class = $this->getErrorMessage() != "" ? " is-invalid" : "";
        $url = GetUrl("captcha/" . $Page->PageID);
        $width = $this->getWidth();
        $height = $this->getHeight() - 1; // Make sure the clipped area does not contain the other part
        $nonce = Nonce();
        return <<<EOT
            <div class="row ew-captcha">
                <div{$classAttr}>
                    <p><img src="{$url}" alt="" class="ew-captcha-image"></p>
                    <input type="text" name="{$this->getElementName()}" id="{$this->getElementId()}" class="form-control ew-form-control{$class}" size="30" placeholder="{$this->language->phrase("EnterValidateCode", true)}">
                    <div class="invalid-feedback">{$this->getErrorMessage()}</div>
                </div>
            </div>
            EOT;
    }

    // HTML tag for confirm page
    public function getConfirmHtml(): string
    {
        return '<input type="hidden" name="' . $this->getElementName() . '" id="' . $this->getElementId() . '" value="' . HtmlEncode($this->Response) . '">';
    }

    // Validate
    public function validate(): bool
    {
        $sessionName = AddTabId($this->getSessionName());
        return $this->Response == Session($sessionName);
    }

    // Client side validation script
    public function getScript(): string
    {
        return '.addField("' . $this->getElementName() . '", ew.Validators.captcha, ' . ($this->getErrorMessage() != '' ? 'true' : 'false') . ')';
    }
}
