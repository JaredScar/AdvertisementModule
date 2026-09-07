<?php
/**
 * StaffCP create/edit advertisement form.
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

if (!$user->handlePanelPageLoad(AdvertisementModule::PERM_CREATE)) {
    require_once ROOT_PATH . '/403.php';
    die();
}

const PAGE = 'panel';
const PARENT_PAGE = 'advertisement_divider';
const PANEL_PAGE = 'advertisement_create';

require_once ROOT_PATH . '/modules/AdvertisementModule/classes/AdvertisementHelper.php';

$editing = false;
$advertisement = null;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $advertisement = AdvertisementHelper::getAd((int) $_GET['id']);
    if ($advertisement === null) {
        Session::flash('advertisement_error', $advertisement_language->get('advertisement', 'advertisement_not_found'));
        Redirect::to(URL::build('/panel/advertisements'));
    }
    $editing = true;
}

$page_title = $editing
    ? $advertisement_language->get('advertisement', 'edit_advertisement')
    : $advertisement_language->get('advertisement', 'create_advertisement');

require_once ROOT_PATH . '/core/templates/backend_init.php';

$errors = [];

if (Input::exists()) {
    if (Token::check()) {
        $validation = Validate::check($_POST, [
            'name' => [
                Validate::REQUIRED => true,
                Validate::MIN => 1,
                Validate::MAX => 128,
            ],
            'content' => [
                Validate::REQUIRED => true,
                Validate::MAX => 100000,
            ],
            'location' => [
                Validate::REQUIRED => true,
            ],
        ])->messages([
            'name' => $advertisement_language->get('advertisement', 'name_required'),
            'content' => $advertisement_language->get('advertisement', 'content_required'),
        ]);

        $location = (string) Input::get('location');
        if (!AdvertisementHelper::isValidLocation($location)) {
            $errors[] = $advertisement_language->get('advertisement', 'invalid_location');
        }

        $orderRaw = Input::get('order');
        if ($orderRaw !== '' && $orderRaw !== null && !is_numeric($orderRaw)) {
            $errors[] = $advertisement_language->get('advertisement', 'invalid_order');
        }

        if ($validation->passed() && !count($errors)) {
            $name = AdvertisementHelper::sanitizePlainText((string) Input::get('name'), 128);
            if ($name === '') {
                $errors[] = $advertisement_language->get('advertisement', 'name_required');
            }

            $content = AdvertisementHelper::sanitizeHtml((string) Input::get('content'));
            if ($content === '') {
                $errors[] = $advertisement_language->get('advertisement', 'content_required');
            }

            if (!count($errors)) {
                $enabled = Input::get('enabled') === '1' ? 1 : 0;
                $order = (int) Input::get('order');
                $now = date('U');

                try {
                    if ($editing) {
                        DB::getInstance()->update('advertisements', $advertisement->id, [
                            'name' => $name,
                            'content' => $content,
                            'location' => $location,
                            'enabled' => $enabled,
                            'order' => $order,
                            'updated' => $now,
                        ]);
                        Session::flash('advertisement_success', $advertisement_language->get('advertisement', 'advertisement_updated'));
                    } else {
                        DB::getInstance()->insert('advertisements', [
                            'creator_id' => $user->data()->id,
                            'name' => $name,
                            'content' => $content,
                            'location' => $location,
                            'enabled' => $enabled,
                            'order' => $order,
                            'created' => $now,
                            'updated' => $now,
                        ]);
                        Session::flash('advertisement_success', $advertisement_language->get('advertisement', 'advertisement_created'));
                    }

                    Redirect::to(URL::build('/panel/advertisements'));
                } catch (Exception $e) {
                    $errors[] = $e->getMessage();
                }
            }
        } else {
            $errors = array_merge($errors, $validation->errors());
        }
    } else {
        $errors[] = $language->get('general', 'invalid_token');
    }
}

Module::loadPage($user, $pages, $cache, $smarty, [$navigation, $cc_nav, $staffcp_nav], $widgets, $template);

if (count($errors)) {
    $template->getEngine()->addVariables([
        'ERRORS' => $errors,
        'ERRORS_TITLE' => $language->get('general', 'error'),
    ]);
}

$locations = AdvertisementHelper::locations($advertisement_language);
$location_options = [];
foreach ($locations as $value => $label) {
    $location_options[] = [
        'value' => Output::getClean($value),
        'label' => Output::getClean($label),
        'selected' => $editing
            ? ($advertisement->location === $value)
            : ($value === AdvertisementHelper::LOCATION_WIDGET),
    ];
}

$name_value = Input::exists() ? Input::get('name') : ($editing ? $advertisement->name : '');
$content_value = Input::exists() ? Input::get('content') : ($editing ? $advertisement->content : '');
$order_value = Input::exists() ? Input::get('order') : ($editing ? $advertisement->order : '0');
$enabled_value = Input::exists()
    ? (Input::get('enabled') === '1')
    : ($editing ? (bool) $advertisement->enabled : true);

if (Input::exists()) {
    $selected_location = (string) Input::get('location');
    foreach ($location_options as &$option) {
        $option['selected'] = ($option['value'] === Output::getClean($selected_location));
    }
    unset($option);
}

$template->getEngine()->addVariables([
    'PARENT_PAGE' => PARENT_PAGE,
    'DASHBOARD' => $language->get('admin', 'dashboard'),
    'ADVERTISEMENTS' => $advertisement_language->get('advertisement', 'advertisements'),
    'FORM_TITLE' => $page_title,
    'BACK' => $language->get('general', 'back'),
    'BACK_LINK' => URL::build('/panel/advertisements'),
    'NAME' => $advertisement_language->get('advertisement', 'name'),
    'NAME_VALUE' => Output::getClean((string) $name_value),
    'CONTENT' => $advertisement_language->get('advertisement', 'content'),
    'CONTENT_VALUE' => (string) $content_value,
    'CONTENT_INFO' => $advertisement_language->get('advertisement', 'content_info'),
    'LOCATION' => $advertisement_language->get('advertisement', 'location'),
    'LOCATION_OPTIONS' => $location_options,
    'ENABLED' => $advertisement_language->get('advertisement', 'enabled'),
    'ENABLED_VALUE' => $enabled_value,
    'ORDER' => $advertisement_language->get('advertisement', 'order'),
    'ORDER_VALUE' => Output::getClean((string) $order_value),
    'PAGE' => PANEL_PAGE,
    'TOKEN' => Token::get(),
    'SUBMIT' => $language->get('general', 'submit'),
]);

$template->onPageLoad();

require ROOT_PATH . '/core/templates/panel_navbar.php';

$template->displayTemplate('advertisement/form');
