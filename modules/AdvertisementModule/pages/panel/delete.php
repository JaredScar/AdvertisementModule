<?php
/**
 * StaffCP delete advertisement.
 *
 * @author JaredScar
 * @license MIT
 * @version 2.0.0
 *
 * @var Cache $cache
 * @var FakeSmarty $smarty
 * @var Language $advertisement_language
 * @var Language $language
 * @var Navigation $cc_nav
 * @var Navigation $navigation
 * @var Navigation $staffcp_nav
 * @var Pages $pages
 * @var TemplateBase $template
 * @var User $user
 * @var Widgets $widgets
 */

if (!$user->handlePanelPageLoad(AdvertisementModule::PERM_DELETE)) {
    require_once ROOT_PATH . '/403.php';
    die();
}

const PAGE = 'panel';
const PARENT_PAGE = 'advertisement_divider';
const PANEL_PAGE = 'advertisement_manage';

require_once ROOT_PATH . '/modules/AdvertisementModule/classes/AdvertisementHelper.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    Redirect::to(URL::build('/panel/advertisements'));
}

$advertisement = AdvertisementHelper::getAd((int) $_GET['id']);
if ($advertisement === null) {
    Session::flash('advertisement_error', $advertisement_language->get('advertisement', 'advertisement_not_found'));
    Redirect::to(URL::build('/panel/advertisements'));
}

$page_title = $advertisement_language->get('advertisement', 'delete_advertisement');
require_once ROOT_PATH . '/core/templates/backend_init.php';

if (Input::exists()) {
    if (Token::check()) {
        AdvertisementHelper::deleteAd((int) $advertisement->id);
        Session::flash('advertisement_success', $advertisement_language->get('advertisement', 'advertisement_deleted'));
        Redirect::to(URL::build('/panel/advertisements'));
    }

    Session::flash('advertisement_error', $language->get('general', 'invalid_token'));
    Redirect::to(URL::build('/panel/advertisements/delete/', 'id=' . urlencode((string) $advertisement->id)));
}

Module::loadPage($user, $pages, $cache, $smarty, [$navigation, $cc_nav, $staffcp_nav], $widgets, $template);

$template->getEngine()->addVariables([
    'PARENT_PAGE' => PARENT_PAGE,
    'DASHBOARD' => $language->get('admin', 'dashboard'),
    'ADVERTISEMENTS' => $advertisement_language->get('advertisement', 'advertisements'),
    'DELETE_ADVERTISEMENT' => $advertisement_language->get('advertisement', 'delete_advertisement'),
    'CONFIRM_DELETE' => $advertisement_language->get('advertisement', 'confirm_delete'),
    'NAME' => $advertisement_language->get('advertisement', 'name'),
    'NAME_VALUE' => Output::getClean($advertisement->name),
    'BACK' => $language->get('general', 'back'),
    'BACK_LINK' => URL::build('/panel/advertisements'),
    'PAGE' => PANEL_PAGE,
    'TOKEN' => Token::get(),
    'SUBMIT' => $language->get('general', 'submit'),
]);

$template->onPageLoad();

require ROOT_PATH . '/core/templates/panel_navbar.php';

$template->displayTemplate('advertisement/delete');
