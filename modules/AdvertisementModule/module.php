<?php
/**
 * Advertisement Module
 *
 * @author JaredScar
 * @license MIT
 * @version 2.0.0
 */

class AdvertisementModule extends Module {

    public const PERM_CREATE = 'advertisement.create';
    public const PERM_DELETE = 'advertisement.delete';
    public const PERM_SETTINGS = 'advertisement.settings';
    public const PERM_VIEW_STATS = 'advertisement.view_stats';

    public const WIDGET_SIDEBAR = 'Ads';
    public const WIDGET_HEADER = 'Ads Header';
    public const WIDGET_FOOTER = 'Ads Footer';

    private Language $_language;
    private Language $_advertisement_language;

    public function __construct(Language $language, Language $advertisement_language, Pages $pages) {
        $this->_language = $language;
        $this->_advertisement_language = $advertisement_language;

        $name = 'AdvertisementModule';
        $author = '<a href="https://github.com/JaredScar" target="_blank" rel="noopener noreferrer">JaredScar</a>';
        $module_version = '2.0.0';
        $nameless_version = '2.2.5';

        parent::__construct($this, $name, $author, $module_version, $nameless_version);

        $pages->add($name, '/panel/advertisements', 'pages/panel/index.php');
        $pages->add($name, '/panel/advertisements/form', 'pages/panel/form.php');
        $pages->add($name, '/panel/advertisements/delete', 'pages/panel/delete.php');
        $pages->add($name, '/panel/advertisements/stats', 'pages/panel/stats.php');
        $pages->add($name, '/queries/ads/click', 'pages/queries/click.php');
    }

    public function onInstall() {
        $db = DB::getInstance();

        try {
            $this->installOrUpgradeSchema($db);
            Settings::set('widget_enabled', '1', AdvertisementHelper::MODULE_SETTINGS);
            Settings::set('header_enabled', '1', AdvertisementHelper::MODULE_SETTINGS);
            Settings::set('footer_enabled', '1', AdvertisementHelper::MODULE_SETTINGS);

            AdvertisementHelper::grantPermissionsToAdminGroup([
                self::PERM_CREATE,
                self::PERM_DELETE,
                self::PERM_SETTINGS,
                self::PERM_VIEW_STATS,
            ]);
        } catch (Exception $e) {
            ErrorHandler::logWarning('AdvertisementModule install error: ' . $e->getMessage());
        }
    }

    public function onUninstall() {
        $db = DB::getInstance();

        try {
            $db->query('DROP TABLE IF EXISTS nl2_advertisement_stats');
            $db->query('DROP TABLE IF EXISTS nl2_advertisements');

            Settings::set('widget_enabled', null, AdvertisementHelper::MODULE_SETTINGS);
            Settings::set('header_enabled', null, AdvertisementHelper::MODULE_SETTINGS);
            Settings::set('footer_enabled', null, AdvertisementHelper::MODULE_SETTINGS);

            foreach ([self::WIDGET_SIDEBAR, self::WIDGET_HEADER, self::WIDGET_FOOTER] as $widgetName) {
                $db->delete('widgets', ['name', $widgetName]);
            }
        } catch (Exception $e) {
            ErrorHandler::logWarning('AdvertisementModule uninstall error: ' . $e->getMessage());
        }
    }

    public function onEnable() {
        // No-op
    }

    public function onDisable() {
        // No-op
    }

    public function onPageLoad(User $user, Pages $pages, Cache $cache, $smarty, iterable $navs, Widgets $widgets, TemplateBase $template) {
        require_once ROOT_PATH . '/modules/AdvertisementModule/classes/AdvertisementHelper.php';
        require_once ROOT_PATH . '/modules/AdvertisementModule/widgets/AdvertisementWidget.php';
        require_once ROOT_PATH . '/modules/AdvertisementModule/widgets/AdvertisementHeaderWidget.php';
        require_once ROOT_PATH . '/modules/AdvertisementModule/widgets/AdvertisementFooterWidget.php';

        PermissionHandler::registerPermissions($this->_advertisement_language->get('advertisement', 'module_name'), [
            self::PERM_CREATE => $this->_advertisement_language->get('advertisement', 'permission_create'),
            self::PERM_DELETE => $this->_advertisement_language->get('advertisement', 'permission_delete'),
            self::PERM_SETTINGS => $this->_advertisement_language->get('advertisement', 'permission_settings'),
            self::PERM_VIEW_STATS => $this->_advertisement_language->get('advertisement', 'permission_view_stats'),
        ]);

        $activePage = $pages->getActivePage();
        if (($activePage['widgets'] ?? false) || (defined('PANEL_PAGE') && str_contains((string) PANEL_PAGE, 'widget'))) {
            $widgets->add(new AdvertisementWidget($this->_advertisement_language));
            $widgets->add(new AdvertisementHeaderWidget($this->_advertisement_language));
            $widgets->add(new AdvertisementFooterWidget($this->_advertisement_language));

            // Ensure locations after widgets are registered/created in DB
            AdvertisementHelper::ensureWidgetLocation(self::WIDGET_HEADER, 'top');
            AdvertisementHelper::ensureWidgetLocation(self::WIDGET_FOOTER, 'footer');
        }

        if (defined('BACK_END')) {
            $cache->setCache('panel_sidebar');

            $canCreate = $user->hasPermission(self::PERM_CREATE);
            $canSettings = $user->hasPermission(self::PERM_SETTINGS);
            $canStats = $user->hasPermission(self::PERM_VIEW_STATS);
            $canDelete = $user->hasPermission(self::PERM_DELETE);

            if ($canCreate || $canSettings || $canStats || $canDelete) {
                if (!$cache->isCached('advertisement_order')) {
                    $order = 25;
                    $cache->store('advertisement_order', $order);
                } else {
                    $order = $cache->retrieve('advertisement_order');
                }

                if (!$cache->isCached('advertisement_icon')) {
                    $icon = '<i class="nav-icon fas fa-ad"></i>';
                    $cache->store('advertisement_icon', $icon);
                } else {
                    $icon = $cache->retrieve('advertisement_icon');
                }

                $navs[2]->add(
                    'advertisement_divider',
                    mb_strtoupper($this->_advertisement_language->get('advertisement', 'module_name'), 'UTF-8'),
                    'divider',
                    'top',
                    null,
                    $order
                );

                if ($canCreate || $canSettings) {
                    $navs[2]->add(
                        'advertisement_manage',
                        $this->_advertisement_language->get('advertisement', 'manage_advertisements'),
                        URL::build('/panel/advertisements'),
                        'top',
                        null,
                        $order + 0.1,
                        $icon
                    );
                }

                if ($canCreate) {
                    $navs[2]->add(
                        'advertisement_create',
                        $this->_advertisement_language->get('advertisement', 'create_advertisement'),
                        URL::build('/panel/advertisements/form'),
                        'top',
                        null,
                        $order + 0.2,
                        '<i class="nav-icon fas fa-plus"></i>'
                    );
                }

                if ($canStats) {
                    $navs[2]->add(
                        'advertisement_stats',
                        $this->_advertisement_language->get('advertisement', 'statistics'),
                        URL::build('/panel/advertisements/stats'),
                        'top',
                        null,
                        $order + 0.3,
                        '<i class="nav-icon fas fa-chart-bar"></i>'
                    );
                }
            }
        }
    }

    public function getDebugInfo(): array {
        require_once ROOT_PATH . '/modules/AdvertisementModule/classes/AdvertisementHelper.php';

        try {
            return [
                'widget_enabled' => Settings::get('widget_enabled', '1', AdvertisementHelper::MODULE_SETTINGS),
                'header_enabled' => Settings::get('header_enabled', '1', AdvertisementHelper::MODULE_SETTINGS),
                'footer_enabled' => Settings::get('footer_enabled', '1', AdvertisementHelper::MODULE_SETTINGS),
                'advertisement_count' => (int) (DB::getInstance()->query('SELECT COUNT(*) AS c FROM nl2_advertisements')->first()->c ?? 0),
            ];
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }

    private function installOrUpgradeSchema(DB $db): void {
        if (!$db->showTables('advertisements')) {
            $db->createTable('advertisements', '
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `creator_id` INT(11) NOT NULL,
                `name` VARCHAR(128) NOT NULL,
                `content` MEDIUMTEXT NOT NULL,
                `location` VARCHAR(16) NOT NULL DEFAULT \'widget\',
                `enabled` TINYINT(1) NOT NULL DEFAULT 1,
                `order` INT(11) NOT NULL DEFAULT 0,
                `created` INT(11) NOT NULL,
                `updated` INT(11) NOT NULL,
                PRIMARY KEY (`id`),
                INDEX (`location`),
                INDEX (`enabled`)
            ');
        } else {
            $this->upgradeAdvertisementsTable($db);
        }

        if (!$db->showTables('advertisement_stats')) {
            $db->createTable('advertisement_stats', '
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `advertisement_id` INT(11) NOT NULL,
                `date` DATE NOT NULL,
                `impressions` INT(11) NOT NULL DEFAULT 0,
                `clicks` INT(11) NOT NULL DEFAULT 0,
                PRIMARY KEY (`id`),
                UNIQUE KEY `ad_date` (`advertisement_id`, `date`),
                INDEX (`advertisement_id`),
                INDEX (`date`)
            ');
        } else {
            $this->upgradeStatsTable($db);
        }
    }

    private function upgradeAdvertisementsTable(DB $db): void {
        $hasOldHeader = $db->query("SHOW COLUMNS FROM nl2_advertisements LIKE 'ad_header'")->count() > 0;
        if (!$hasOldHeader) {
            return;
        }

        // Legacy scaffold schema → current schema
        if ($db->query("SHOW COLUMNS FROM nl2_advertisements LIKE 'name'")->count() === 0) {
            $db->addColumn('advertisements', '`name`', 'VARCHAR(128) NOT NULL DEFAULT \'\' AFTER `creator_id`');
        }
        if ($db->query("SHOW COLUMNS FROM nl2_advertisements LIKE 'content'")->count() === 0) {
            $db->addColumn('advertisements', '`content`', 'MEDIUMTEXT NULL AFTER `name`');
        }
        if ($db->query("SHOW COLUMNS FROM nl2_advertisements LIKE 'location'")->count() === 0) {
            $db->addColumn('advertisements', '`location`', 'VARCHAR(16) NOT NULL DEFAULT \'widget\' AFTER `content`');
        }
        if ($db->query("SHOW COLUMNS FROM nl2_advertisements LIKE 'enabled'")->count() === 0) {
            $db->addColumn('advertisements', '`enabled`', 'TINYINT(1) NOT NULL DEFAULT 1 AFTER `location`');
        }
        if ($db->query("SHOW COLUMNS FROM nl2_advertisements LIKE 'order'")->count() === 0) {
            $db->addColumn('advertisements', '`order`', 'INT(11) NOT NULL DEFAULT 0 AFTER `enabled`');
        }
        if ($db->query("SHOW COLUMNS FROM nl2_advertisements LIKE 'created'")->count() === 0) {
            $db->addColumn('advertisements', '`created`', 'INT(11) NOT NULL DEFAULT 0 AFTER `order`');
        }
        if ($db->query("SHOW COLUMNS FROM nl2_advertisements LIKE 'updated'")->count() === 0) {
            $db->addColumn('advertisements', '`updated`', 'INT(11) NOT NULL DEFAULT 0 AFTER `created`');
        }

        $db->query('UPDATE nl2_advertisements SET
            `name` = IF(`name` = \'\' OR `name` IS NULL, IFNULL(`ad_header`, CONCAT(\'Ad #\', `ad_id`)), `name`),
            `content` = IF(`content` IS NULL OR `content` = \'\', IFNULL(`ad_content`, \'\'), `content`),
            `created` = IF(`created` = 0, UNIX_TIMESTAMP(IFNULL(`creation_timestamp`, NOW())), `created`),
            `updated` = IF(`updated` = 0, UNIX_TIMESTAMP(IFNULL(`creation_timestamp`, NOW())), `updated`)
        ');

        // Rename primary key column if needed
        if ($db->query("SHOW COLUMNS FROM nl2_advertisements LIKE 'ad_id'")->count() > 0
            && $db->query("SHOW COLUMNS FROM nl2_advertisements LIKE 'id'")->count() === 0) {
            $db->query('ALTER TABLE nl2_advertisements CHANGE `ad_id` `id` INT(11) NOT NULL AUTO_INCREMENT');
        }
    }

    private function upgradeStatsTable(DB $db): void {
        $hasOld = $db->query("SHOW COLUMNS FROM nl2_advertisement_stats LIKE 'imps'")->count() > 0;
        if (!$hasOld) {
            return;
        }

        if ($db->query("SHOW COLUMNS FROM nl2_advertisement_stats LIKE 'advertisement_id'")->count() === 0) {
            $db->addColumn('advertisement_stats', '`advertisement_id`', 'INT(11) NULL AFTER `ad_id`');
            $db->query('UPDATE nl2_advertisement_stats SET `advertisement_id` = `ad_id`');
        }
        if ($db->query("SHOW COLUMNS FROM nl2_advertisement_stats LIKE 'impressions'")->count() === 0) {
            $db->addColumn('advertisement_stats', '`impressions`', 'INT(11) NOT NULL DEFAULT 0');
            $db->query('UPDATE nl2_advertisement_stats SET `impressions` = IFNULL(`imps`, 0)');
        }
        if ($db->query("SHOW COLUMNS FROM nl2_advertisement_stats LIKE 'date'")->count() === 0) {
            $db->addColumn('advertisement_stats', '`date`', 'DATE NULL');
            $db->query('UPDATE nl2_advertisement_stats SET `date` = DATE(IFNULL(`datetime`, NOW()))');
        }
        if ($db->query("SHOW COLUMNS FROM nl2_advertisement_stats LIKE 'id'")->count() === 0) {
            $db->query('ALTER TABLE nl2_advertisement_stats ADD `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST');
        }
    }
}
