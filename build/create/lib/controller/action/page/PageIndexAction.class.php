<?php
class PageIndexAction extends SzAbstractAction
{

    /**
     * Display the index page.
     *
     * @return SzResponse
     */
    public function execute()
    {
        $smarty = SzSmarty::get();
        $appConfig = SzConfig::get()->loadAppConfig('app');

        $smarty->assign('WEB_HOST', $appConfig['WEB_HOST']);
        $smarty->assign('CDN_HOST', $appConfig['CDN_HOST']);
        $smarty->assign('WWW_RELATIVE_PATH', SzSystem::$WWW_RELATIVE_PATH);
        $smarty->assign('CANVAS_URL', $appConfig['CANVAS_URL']);
        $smarty->assign('JS_VER', $appConfig['JS_VER']);
        $smarty->assign('CSS_VER', $appConfig['CSS_VER']);

        $response = $this->buildResponse(
            $smarty->fetch('index.tpl')
        );
        $response->setContentType('text/html');

        return $response;
    }

}