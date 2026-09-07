<?php
/**
 * Sidebar advertisements widget.
 *
 * @author JaredScar
 * @license MIT
 * @version 2.0.0
 */

class AdvertisementWidget extends WidgetBase {

    private Language $_language;

    public function __construct(Language $language) {
        $this->_module = 'AdvertisementModule';
        $this->_name = AdvertisementModule::WIDGET_SIDEBAR;
        $this->_description = $language->get('advertisement', 'widget_description');
        $this->_language = $language;

        $this->bootstrapWidget('right');
    }

    public function initialise(): void {
        $ads = AdvertisementHelper::getEnabledAdsByLocation(AdvertisementHelper::LOCATION_WIDGET);
        $content = AdvertisementHelper::renderAds($ads);

        if ($content === '') {
            $this->_content = '';
            return;
        }

        $title = Output::getClean($this->_language->get('advertisement', 'advertisements'));
        $this->_content = '<div class="ui fluid card" id="widget-advertisements"><div class="content"><h4 class="ui header">'
            . $title
            . '</h4><div class="description">'
            . $content
            . '</div></div></div>';
    }

    private function bootstrapWidget(string $location): void {
        $db = DB::getInstance();
        $row = $db->get('widgets', ['name', $this->_name])->first();
        if (!$row) {
            $db->insert('widgets', [
                'name' => $this->_name,
                'enabled' => false,
                'location' => $location,
                'order' => 10,
                'pages' => '["index","forum"]',
            ]);
        }
    }
}
