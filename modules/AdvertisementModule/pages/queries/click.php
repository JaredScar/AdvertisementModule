<?php
/**
 * Advertisement click tracker + safe redirect.
 *
 * @author JaredScar
 * @license MIT
 * @version 2.0.0
 */

require_once ROOT_PATH . '/modules/AdvertisementModule/classes/AdvertisementHelper.php';

$id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int) $_GET['id'] : 0;
$url = isset($_GET['url']) ? rawurldecode((string) $_GET['url']) : '';

$fallback = URL::build('/');

if ($id <= 0 || $url === '' || !AdvertisementHelper::isSafeExternalUrl($url)) {
    Redirect::to($fallback);
}

$ad = AdvertisementHelper::getAd($id);
if ($ad === null || !(int) $ad->enabled || !AdvertisementHelper::urlBelongsToAd($ad, $url)) {
    Redirect::to($fallback);
}

AdvertisementHelper::recordClick($id);

// Relative URLs stay on-site; absolute http(s) leave the site
if (str_starts_with($url, '/') && !str_starts_with($url, '//')) {
    Redirect::to($url);
}

Redirect::to($url);
