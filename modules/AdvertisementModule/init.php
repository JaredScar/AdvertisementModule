<?php
/**
 * Advertisement Module initialisation.
 *
 * @author JaredScar
 * @license MIT
 * @version 2.0.0
 */

require_once ROOT_PATH . '/modules/AdvertisementModule/module.php';
require_once ROOT_PATH . '/modules/AdvertisementModule/classes/AdvertisementHelper.php';

$advertisement_language = new Language(ROOT_PATH . '/modules/AdvertisementModule/language');

$module = new AdvertisementModule($language, $advertisement_language, $pages);
