<?php
/**
 * StaffCP advertisements list + global settings.
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

$user->handlePanelPageLoad();

if (
    !$user->hasPermission(AdvertisementModule::PERM_CREATE)
    && !$user->hasPermission(AdvertisementModule::PERM_SETTINGS)
) {
    require_once ROOT_PATH . '/403.php';
    die();
}

const PAGE = 'panel';
const PARENT_PAGE = 'advertisement_divider';
const PANEL_PAGE = 'advertisement_manage';

$page_title = $advertisement_language->get('advertisement', 'manage_advertisements');
require_once ROOT_PATH . '/core/templates/backend_init.php';
require_once ROOT_PATH . '/modules/AdvertisementModule/classes/AdvertisementHelper.php';

if (Input::exists() && $user->hasPermission(AdvertisementModule::PERM_SETTINGS)) {
    if (Token::check()) {
        Settings::set(
            'widget_enabled',
            Input::get('widget_enabled') === '1' ? '1' : '0',
            AdvertisementHelper::MODULE_SETTINGS
        );
        Settings::set(
            'header_enabled',
            Input::get('header_enabled') === '1' ? '1' : '0',
            AdvertisementHelper::MODULE_SETTINGS
        );
        Settings::set(
            'footer_enabled',
            Input::get('footer_enabled') === '1' ? '1' : '0',
            AdvertisementHelper::MODULE_SETTINGS
        );

        Session::flash('advertisement_success', $advertisement_language->get('advertisement', 'settings_updated'));
    } else {
        Session::flash('advertisement_error', $language->get('general', 'invalid_token'));
    }

    Redirect::to(URL::build('/panel/advertisements'));
}

Module::loadPage($user, $pages, $cache, $smarty, [$navigation, $cc_nav, $staffcp_nav], $widgets, $template);

if (Session::exists('advertisement_success')) {
    $template->getEngine()->addVariables([
        'SUCCESS' => Session::flash('advertisement_success'),
        'SUCCESS_TITLE' => $language->get('general', 'success'),
    ]);
}

if (Session::exists('advertisement_error')) {
    $template->getEngine()->addVariables([
        'ERROR' => Session::flash('advertisement_error'),
        'ERRORS_TITLE' => $language->get('general', 'error'),
    ]);
}

$locations = AdvertisementHelper::locations($advertisement_language);
$ads = DB::getInstance()->query('SELECT * FROM nl2_advertisements ORDER BY `order` ASC, `id` ASC')->results();
$ads_list = [];

foreach ($ads as $ad) {
    $creator = new User($ad->creator_id);
    $ads_list[] = [
        'id' => Output::getClean((string) $ad->id),
        'name' => Output::getClean($ad->name),
        'location' => Output::getClean($locations[$ad->location] ?? $ad->location),
        'location_raw' => Output::getClean($ad->location),
        'enabled' => (bool) $ad->enabled,
        'order' => Output::getClean((string) $ad->order),
        'creator' => $creator->exists() ? Output::getClean($creator->getDisplayname()) : '-',
        'edit_link' => URL::build('/panel/advertisements/form/', 'id=' . urlencode((string) $ad->id)),
        'delete_link' => URL::build('/panel/advertisements/delete/', 'id=' . urlencode((string) $ad->id)),
        'stats_link' => URL::build('/panel/advertisements/stats/', 'id=' . urlencode((string) $ad->id)),
    ];
}

$template->getEngine()->addVariables([
    'PARENT_PAGE' => PARENT_PAGE,
    'DASHBOARD' => $language->get('admin', 'dashboard'),
    'ADVERTISEMENTS' => $advertisement_language->get('advertisement', 'advertisements'),
    'MANAGE_ADVERTISEMENTS' => $advertisement_language->get('advertisement', 'manage_advertisements'),
    'GLOBAL_SETTINGS' => $advertisement_language->get('advertisement', 'global_settings'),
    'PLACEMENT_INFO' => $advertisement_language->get('advertisement', 'placement_info'),
    'ENABLE_WIDGET' => $advertisement_language->get('advertisement', 'enable_widget_placement'),
    'ENABLE_HEADER' => $advertisement_language->get('advertisement', 'enable_header_placement'),
    'ENABLE_FOOTER' => $advertisement_language->get('advertisement', 'enable_footer_placement'),
    'WIDGET_ENABLED' => Settings::get('widget_enabled', '1', AdvertisementHelper::MODULE_SETTINGS) === '1',
    'HEADER_ENABLED' => Settings::get('header_enabled', '1', AdvertisementHelper::MODULE_SETTINGS) === '1',
    'FOOTER_ENABLED' => Settings::get('footer_enabled', '1', AdvertisementHelper::MODULE_SETTINGS) === '1',
    'CAN_SETTINGS' => $user->hasPermission(AdvertisementModule::PERM_SETTINGS),
    'CAN_CREATE' => $user->hasPermission(AdvertisementModule::PERM_CREATE),
    'CAN_DELETE' => $user->hasPermission(AdvertisementModule::PERM_DELETE),
    'CAN_STATS' => $user->hasPermission(AdvertisementModule::PERM_VIEW_STATS),
    'NEW_LINK' => URL::build('/panel/advertisements/form'),
    'NEW' => $advertisement_language->get('advertisement', 'new'),
    'NAME' => $advertisement_language->get('advertisement', 'name'),
    'LOCATION' => $advertisement_language->get('advertisement', 'location'),
    'ENABLED' => $advertisement_language->get('advertisement', 'enabled'),
    'ORDER' => $advertisement_language->get('advertisement', 'order'),
    'CREATOR' => $advertisement_language->get('advertisement', 'creator'),
    'ACTIONS' => $advertisement_language->get('advertisement', 'actions'),
    'NO_ADVERTISEMENTS' => $advertisement_language->get('advertisement', 'no_advertisements'),
    'STATISTICS' => $advertisement_language->get('advertisement', 'statistics'),
    'ADS_LIST' => $ads_list,
    'PAGE' => PANEL_PAGE,
    'TOKEN' => Token::get(),
    'SUBMIT' => $language->get('general', 'submit'),
    'YES' => $language->get('general', 'yes'),
    'NO' => $language->get('general', 'no'),
]);

$template->onPageLoad();

require ROOT_PATH . '/core/templates/panel_navbar.php';

$template->displayTemplate('advertisement/index');
