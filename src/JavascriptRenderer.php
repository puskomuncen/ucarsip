<?php

namespace PHPMaker2025\ucarsip;

use DebugBar\JavascriptRenderer as BaseJavascriptRenderer;

/**
 * Renders the debug bar using the client side javascript implementation
 *
 * Generates all the needed initialization code of controls
 */
class JavascriptRenderer extends BaseJavascriptRenderer
{
    // Override
    protected $enableJqueryNoConflict = false;
    protected $ajaxHandlerBindToFetch = true; // Bind to fetch
    protected $ajaxHandlerBindToJquery = true; // Bind to jQuery
    protected $ajaxHandlerBindToXHR = false;

    // Remove jQuery
    protected $jsVendors = [
        'highlightjs' => 'vendor/highlightjs/highlight.pack.js'
    ];

    /**
     * @param PhpDebugBar $debugBar
     * @param string $baseUrl
     * @param string $basePath
     */
    public function __construct(PhpDebugBar $debugBar, $baseUrl = null, $basePath = null)
    {
        parent::__construct($debugBar, $baseUrl, $basePath);
        $this->jsFiles = [
            '../../../../../../jquery/debugbar.js',
            '../../../../../../jquery/widgets.js',
            'openhandler.js'
        ];
    }

    /**
     * Renders the html to include needed assets
     *
     * Only useful if Assetic is not used
     *
     * @return string
     */
    public function renderHead()
    {
        $html = '';
        list($cssFiles, $jsFiles, $inlineCss, $inlineJs, $inlineHead) = $this->getAssets(null, self::RELATIVE_URL);
        $nonce = $this->getNonceAttribute();
        foreach ($cssFiles as $file) {
            $html .= sprintf('<link rel="stylesheet" href="%s">' . "\n", $file);
        }
        foreach ($inlineCss as $content) {
            $html .= sprintf('<style type="text/css">%s</style>' . "\n", $content);
        }
        $html .= sprintf('<script%s>ew.ready("jquery", %s, "debugbar")</script>' . "\n", $nonce, json_encode($jsFiles));
        foreach ($inlineJs as $content) {
            $html .= sprintf('<script%s>%s</script>' . "\n", $nonce, $content);
        }
        foreach ($inlineHead as $content) {
            $html .= $content . "\n";
        }
        if ($this->enableJqueryNoConflict && !$this->useRequireJs) {
            $html .= '<script' . $nonce . '>jQuery.noConflict(true);</script>' . "\n";
        }
        return $html;
    }

    /**
     * Returns the js code needed to initialize the debug bar
     *
     * @return string
     */
    protected function getJsInitializationCode()
    {
        $js = '';
        if (($this->initialization & self::INITIALIZE_CONSTRUCTOR) === self::INITIALIZE_CONSTRUCTOR) {
            $js .= sprintf('var %1$s = ew.%1$s = new %2$s();' . "\n", $this->variableName, $this->javascriptClass); //***
        }
        if (($this->initialization & self::INITIALIZE_CONTROLS) === self::INITIALIZE_CONTROLS) {
            $js .= $this->getJsControlsDefinitionCode($this->variableName);
        }
        if ($this->ajaxHandlerClass) {
            $js .= sprintf("%s.ajaxHandler = new %s(%s, '%s', %s);\n", //***
                $this->variableName,
                $this->ajaxHandlerClass,
                $this->variableName,
                $this->debugBar->getHeaderName(), //***
                $this->ajaxHandlerAutoShow ? 'true' : 'false'
            );
            if ($this->ajaxHandlerBindToFetch) {
                $js .= sprintf("%s.ajaxHandler.bindToFetch();\n", $this->variableName);
            }
            if ($this->ajaxHandlerBindToXHR) {
                $js .= sprintf("%s.ajaxHandler.bindToXHR();\n", $this->variableName);
            }
            if ($this->ajaxHandlerBindToJquery) { //***
                $js .= sprintf("if (jQuery) %s.ajaxHandler.bindToJquery(jQuery);\n", $this->variableName);
            }
        }
        if ($this->openHandlerUrl !== null) {
            $js .= sprintf("%s.setOpenHandler(new %s(%s));\n", $this->variableName,
                $this->openHandlerClass,
                json_encode(array("url" => $this->openHandlerUrl)));
        }
        return $js;
    }

    /**
     * Returns the code needed to display the debug bar
     *
     * AJAX request should not render the initialization code.
     *
     * @param boolean $initialize Whether or not to render the debug bar initialization code
     * @param boolean $renderStackedData Whether or not to render the stacked data
     * @return string
     */
    public function render($initialize = true, $renderStackedData = true)
    {
        $js = '';
        if ($initialize) {
            $js = $this->getJsInitializationCode();
        }
        if ($renderStackedData && $this->debugBar->hasStackedData()) {
            foreach ($this->debugBar->getStackedData() as $id => $data) {
                $js .= $this->getAddDatasetCode($id, $data, '(stacked)');
            }
        }
        $suffix = !$initialize ? '(ajax)' : null;
        $js .= $this->getAddDatasetCode($this->debugBar->getCurrentRequestId(), $this->debugBar->getData(), $suffix);
        $nonce = $this->getNonceAttribute();
	    if ($nonce != '') {
            $js = preg_replace("/<script>/", "<script nonce='{$this->cspNonce}'>", $js);
        }
        return $this->useRequireJs
            ? "<script {$nonce}>\nrequire(['debugbar'], function(PhpDebugBar) {\n{$js}});\n</script>\n"
            : "<script {$nonce}>\nloadjs.ready('debugbar', () => {\n{$js}});\n</script>\n";
    }
}
