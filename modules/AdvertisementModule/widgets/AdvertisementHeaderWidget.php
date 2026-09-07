<?php
/**
 * Header advertisements widget.
 *
 * @author JaredScar
 * @license MIT
 * @version 2.0.0
 */

class AdvertisementHeaderWidget extends WidgetBase {

    private Language $_language;

    public function __construct(Language $language) {
        $this->_module = 'AdvertisementModule';
        $this->_name = AdvertisementModule::WIDGET_HEADER;
        $this->_description = $language->get('advertisement', 'widget_header_description');
        $this->_language = $language;

        $this->bootstrapWidget('top');
    }

    public function initialise(): void {
        AdvertisementHelper::ensureWidgetLocation($this->_name, 'top');

        $ads = AdvertisementHelper::getEnabledAdsByLocation(AdvertisementHelper::LOCATION_HEADER);
        $content = AdvertisementHelper::renderAds($ads);

        if ($content === '') {
            $this->_content = '';
            return;
        }

        $this->_content = '<div class="advertisement-module-header" style="margin: 1rem 0;">' . $content . '</div>';
    }

    private function bootstrapWidget(string $location): void {
        $db = DB::getInstance();
        $row = $db->get('widgets', ['name', $this->_name])->first();
        if (!$row) {
            $db->insert('widgets', [
                'name' => $this->_name,
                'enabled' => false,
                'location' => $location,
                'order' => 5,
                'pages' => '["index","forum"]',
            ]);
        } elseif ($row->location !== $location) {
            $db->update('widgets', $row->id, ['location' => $location]);
        }
    }
}
