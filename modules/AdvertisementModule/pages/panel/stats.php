<?php
/**
 * StaffCP advertisement statistics.
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

if (!$user->handlePanelPageLoad(AdvertisementModule::PERM_VIEW_STATS)) {
    require_once ROOT_PATH . '/403.php';
    die();
}

const PAGE = 'panel';
const PARENT_PAGE = 'advertisement_divider';
const PANEL_PAGE = 'advertisement_stats';

$page_title = $advertisement_language->get('advertisement', 'statistics');
require_once ROOT_PATH . '/core/templates/backend_init.php';
require_once ROOT_PATH . '/modules/AdvertisementModule/classes/AdvertisementHelper.php';

$db = DB::getInstance();
$selected_id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int) $_GET['id'] : null;
$selected_ad = $selected_id ? AdvertisementHelper::getAd($selected_id) : null;

if ($selected_id && $selected_ad === null) {
    Session::flash('advertisement_error', $advertisement_language->get('advertisement', 'advertisement_not_found'));
    Redirect::to(URL::build('/panel/advertisements/stats'));
}

Module::loadPage($user, $pages, $cache, $smarty, [$navigation, $cc_nav, $staffcp_nav], $widgets, $template);

if (Session::exists('advertisement_error')) {
    $template->getEngine()->addVariables([
        'ERROR' => Session::flash('advertisement_error'),
        'ERRORS_TITLE' => $language->get('general', 'error'),
    ]);
}

$all_ads = $db->query('SELECT `id`, `name` FROM nl2_advertisements ORDER BY `name` ASC')->results();
$ad_options = [];
foreach ($all_ads as $ad) {
    $ad_options[] = [
        'id' => Output::getClean((string) $ad->id),
        'name' => Output::getClean($ad->name),
        'selected' => $selected_id === (int) $ad->id,
        'link' => URL::build('/panel/advertisements/stats/', 'id=' . urlencode((string) $ad->id)),
    ];
}

$daily_stats = [];
$total_impressions = 0;
$total_clicks = 0;

if ($selected_ad) {
    $rows = $db->query(
        'SELECT `date`, `impressions`, `clicks` FROM nl2_advertisement_stats WHERE `advertisement_id` = ? ORDER BY `date` DESC LIMIT 90',
        [$selected_ad->id]
    )->results();

    foreach ($rows as $row) {
        $daily_stats[] = [
            'date' => Output::getClean($row->date),
            'impressions' => Output::getClean((string) $row->impressions),
            'clicks' => Output::getClean((string) $row->clicks),
        ];
        $total_impressions += (int) $row->impressions;
        $total_clicks += (int) $row->clicks;
    }
} else {
    $rows = $db->query(
        'SELECT a.id, a.name,
                COALESCE(SUM(s.impressions), 0) AS impressions,
                COALESCE(SUM(s.clicks), 0) AS clicks
         FROM nl2_advertisements a
         LEFT JOIN nl2_advertisement_stats s ON s.advertisement_id = a.id
         GROUP BY a.id, a.name
         ORDER BY a.name ASC'
    )->results();

    foreach ($rows as $row) {
        $daily_stats[] = [
            'id' => Output::getClean((string) $row->id),
            'name' => Output::getClean($row->name),
            'impressions' => Output::getClean((string) $row->impressions),
            'clicks' => Output::getClean((string) $row->clicks),
            'link' => URL::build('/panel/advertisements/stats/', 'id=' . urlencode((string) $row->id)),
        ];
        $total_impressions += (int) $row->impressions;
        $total_clicks += (int) $row->clicks;
    }
}

$template->getEngine()->addVariables([
    'PARENT_PAGE' => PARENT_PAGE,
    'DASHBOARD' => $language->get('admin', 'dashboard'),
    'ADVERTISEMENTS' => $advertisement_language->get('advertisement', 'advertisements'),
    'STATISTICS' => $advertisement_language->get('advertisement', 'statistics'),
    'ALL_ADVERTISEMENTS' => $advertisement_language->get('advertisement', 'all_advertisements'),
    'ALL_LINK' => URL::build('/panel/advertisements/stats'),
    'SELECT_ADVERTISEMENT' => $advertisement_language->get('advertisement', 'select_advertisement'),
    'AD_OPTIONS' => $ad_options,
    'SELECTED_AD' => $selected_ad ? Output::getClean($selected_ad->name) : null,
    'DAILY_BREAKDOWN' => $advertisement_language->get('advertisement', 'daily_breakdown'),
    'TOTALS' => $advertisement_language->get('advertisement', 'totals'),
    'DATE' => $advertisement_language->get('advertisement', 'date'),
    'NAME' => $advertisement_language->get('advertisement', 'name'),
    'IMPRESSIONS' => $advertisement_language->get('advertisement', 'impressions'),
    'CLICKS' => $advertisement_language->get('advertisement', 'clicks'),
    'NO_STATS' => $advertisement_language->get('advertisement', 'no_stats'),
    'STATS' => $daily_stats,
    'TOTAL_IMPRESSIONS' => Output::getClean((string) $total_impressions),
    'TOTAL_CLICKS' => Output::getClean((string) $total_clicks),
    'VIEWING_SINGLE' => $selected_ad !== null,
    'BACK' => $language->get('general', 'back'),
    'BACK_LINK' => URL::build('/panel/advertisements'),
    'PAGE' => PANEL_PAGE,
]);

$template->onPageLoad();

require ROOT_PATH . '/core/templates/panel_navbar.php';

$template->displayTemplate('advertisement/stats');
