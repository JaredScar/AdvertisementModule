<?php
class AdvertisementModule extends Module {

    public static $CREATE_PERMISSION = "advertisement.create";
    public static $DELETE_PERMISSION = "advertisement.delete";
    public static $VIEW_PERMISSION = "advertisement.view_stats";

    public function __construct() {

        $name = 'AdvertisementModule';
        $author = 'JaredScar';
        $module_version = '1.0.0';
        $nameless_version = '2.0.0-pr12';

        parent::__construct($this, $name, $author, $module_version, $nameless_version);

        $this->pages->add($name, '/panel/ad_settings', 'pages/panel/advertisement_settings.php');
        $this->pages->add($name, '/panel/ad_add', 'pages/panel/advertisement_add.php');
        $this->pages->add($name, '/panel/ad_delete', 'pages/panel/advertisement_confirm_delete.php');
        $this->pages->add($name, '/ad_stats', 'pages/advertisement_stats.php');
    }

    public function onInstall() {}

    public function onUninstall() {}

    public function onEnable() {
        try {
            if (!$this->queries->tableExists('advertisements')) {
                // The advertisements table does not exist, we want to create it:
                $this->queries->createTable('advertisements',
                '
                `ad_id` INT(11) NOT NULL AUTO_INCREMENT,
                `creator_id` INT(11) NOT NULL,
                `ad_header` VARCHAR(100),
                `ad_content` VARCHAR(2024),
                `creation_timestamp` DATETIME NOT NULL,
                PRIMARY KEY (`ad_id`)
                ', 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
            }
            if (!$this->queries->tableExists('advertisement_stats')) {
                $this->queries->createTable('advertisement_stats',
                    '
                `ad_id` INT(11) NOT NULL,
                `imps` INT(64),
                `clicks` INT(64),
                `datetime` DATETIME,
                PRIMARY KEY (`ad_id`, `datetime`),
                INDEX(`ad_id`),
                INDEX(`datetime`)
                ', 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
            }
            // Add permissions
            $this->queries->addPermissionGroup(2, self::$CREATE_PERMISSION);
            $this->queries->addPermissionGroup(2, self::$DELETE_PERMISSION);
            $this->queries->addPermissionGroup(2, self::$VIEW_PERMISSION);
        } catch (Exception $e) {
            die("Error Encountered: " . $e->getMessage());
        }
    }

    public function onDisable() {}

    public function onPageLoad($user, $pages, $cache, $smarty, $navs, $widgets, $template) {
        PermissionHandler::registerPermissions($this->getName(), [
            self::$CREATE_PERMISSION => 'Ability to create new advertisements',
            self::$DELETE_PERMISSION => 'Ability to delete advertisements and their data',
            self::$VIEW_PERMISSION => "Ability to view advertisements' statistics"
        ]);
    }

}