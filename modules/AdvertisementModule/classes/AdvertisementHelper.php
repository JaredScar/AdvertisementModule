<?php
/**
 * Advertisement helper utilities.
 *
 * @author JaredScar
 * @license MIT
 * @version 2.0.0
 */

class AdvertisementHelper {

    public const LOCATION_WIDGET = 'widget';
    public const LOCATION_HEADER = 'header';
    public const LOCATION_FOOTER = 'footer';

    public const MODULE_SETTINGS = 'AdvertisementModule';

    /** @var array<int, true> */
    private static array $_impressions_this_request = [];

    /**
     * @return array<string, string>
     */
    public static function locations(Language $language): array {
        return [
            self::LOCATION_WIDGET => $language->get('advertisement', 'location_widget'),
            self::LOCATION_HEADER => $language->get('advertisement', 'location_header'),
            self::LOCATION_FOOTER => $language->get('advertisement', 'location_footer'),
        ];
    }

    public static function isValidLocation(string $location): bool {
        return in_array($location, [
            self::LOCATION_WIDGET,
            self::LOCATION_HEADER,
            self::LOCATION_FOOTER,
        ], true);
    }

    /**
     * Sanitize untrusted HTML for safe storage/display.
     * Strips scripts, event handlers, and other dangerous markup via HTMLPurifier.
     */
    public static function sanitizeHtml(?string $input): string {
        if ($input === null || $input === '') {
            return '';
        }

        return Output::getPurified($input, false, false);
    }

    public static function sanitizePlainText(?string $input, int $maxLength = 128): string {
        $value = trim(strip_tags($input ?? ''));
        if ($value === '') {
            return '';
        }

        return mb_substr($value, 0, $maxLength);
    }

    public static function isPlacementEnabled(string $location): bool {
        $key = match ($location) {
            self::LOCATION_HEADER => 'header_enabled',
            self::LOCATION_FOOTER => 'footer_enabled',
            default => 'widget_enabled',
        };

        return Settings::get($key, '1', self::MODULE_SETTINGS) === '1';
    }

    /**
     * @return object[]
     */
    public static function getEnabledAdsByLocation(string $location): array {
        if (!self::isValidLocation($location) || !self::isPlacementEnabled($location)) {
            return [];
        }

        return DB::getInstance()->query(
            'SELECT * FROM nl2_advertisements WHERE `location` = ? AND `enabled` = 1 ORDER BY `order` ASC, `id` ASC',
            [$location]
        )->results();
    }

    public static function getAd(int $id): ?object {
        $ad = DB::getInstance()->get('advertisements', ['id', $id])->first();

        return $ad ?: null;
    }

    /**
     * Rewrite anchor hrefs through the click tracker and record an impression.
     */
    public static function renderAd(object $ad): string {
        $content = (string) ($ad->content ?? '');
        if ($content === '') {
            return '';
        }

        $content = self::rewriteLinksForTracking((int) $ad->id, $content);
        self::recordImpression((int) $ad->id);

        return $content;
    }

    /**
     * @param object[] $ads
     */
    public static function renderAds(array $ads): string {
        if (!count($ads)) {
            return '';
        }

        $html = '';
        foreach ($ads as $ad) {
            $rendered = self::renderAd($ad);
            if ($rendered === '') {
                continue;
            }

            $html .= '<div class="advertisement-module-ad" data-ad-id="' . Output::getClean((string) $ad->id) . '">'
                . $rendered
                . '</div>';
        }

        return $html;
    }

    public static function recordImpression(int $adId): void {
        if ($adId <= 0 || isset(self::$_impressions_this_request[$adId])) {
            return;
        }

        self::$_impressions_this_request[$adId] = true;
        self::incrementStat($adId, 'impressions');
    }

    public static function recordClick(int $adId): void {
        if ($adId <= 0) {
            return;
        }

        self::incrementStat($adId, 'clicks');
    }

    private static function incrementStat(int $adId, string $column): void {
        if (!in_array($column, ['impressions', 'clicks'], true)) {
            return;
        }

        $db = DB::getInstance();
        $date = date('Y-m-d');

        $existing = $db->query(
            'SELECT `id` FROM nl2_advertisement_stats WHERE `advertisement_id` = ? AND `date` = ?',
            [$adId, $date]
        )->first();

        if ($existing) {
            $db->query(
                "UPDATE nl2_advertisement_stats SET `{$column}` = `{$column}` + 1 WHERE `id` = ?",
                [$existing->id]
            );
            return;
        }

        $db->insert('advertisement_stats', [
            'advertisement_id' => $adId,
            'date' => $date,
            'impressions' => $column === 'impressions' ? 1 : 0,
            'clicks' => $column === 'clicks' ? 1 : 0,
        ]);
    }

    public static function rewriteLinksForTracking(int $adId, string $html): string {
        return (string) preg_replace_callback(
            '/<a\s([^>]*?)href\s*=\s*(["\'])([^"\']+)\2([^>]*)>/i',
            static function (array $matches) use ($adId): string {
                $url = html_entity_decode($matches[3], ENT_QUOTES);
                if (!self::isSafeExternalUrl($url)) {
                    // Drop unsafe links entirely
                    return '<a ' . $matches[1] . 'href="#"' . $matches[4] . '>';
                }

                $trackUrl = URL::build('/queries/ads/click', 'id=' . urlencode((string) $adId) . '&url=' . rawurlencode($url));

                return '<a ' . $matches[1] . 'href="' . Output::getClean($trackUrl) . '"' . $matches[4] . ' rel="noopener noreferrer">';
            },
            $html
        );
    }

    public static function isSafeExternalUrl(string $url): bool {
        $url = trim($url);
        if ($url === '' || str_starts_with($url, '#')) {
            return false;
        }

        // Allow relative site paths
        if (str_starts_with($url, '/') && !str_starts_with($url, '//')) {
            return true;
        }

        $parts = parse_url($url);
        if ($parts === false || !isset($parts['scheme'], $parts['host'])) {
            return false;
        }

        $scheme = strtolower($parts['scheme']);
        return in_array($scheme, ['http', 'https'], true);
    }

    /**
     * Validate that a destination URL was present in the stored (already purified) ad content.
     */
    public static function urlBelongsToAd(object $ad, string $url): bool {
        $content = html_entity_decode((string) $ad->content, ENT_QUOTES);
        $candidates = [
            $url,
            htmlspecialchars($url, ENT_QUOTES),
            rawurlencode($url),
        ];

        foreach ($candidates as $candidate) {
            if ($candidate !== '' && str_contains($content, $candidate)) {
                return true;
            }
        }

        // Also accept if any href in content matches after decode
        if (preg_match_all('/href\s*=\s*(["\'])([^"\']+)\1/i', $content, $matches)) {
            foreach ($matches[2] as $href) {
                if (html_entity_decode($href, ENT_QUOTES) === $url) {
                    return true;
                }
            }
        }

        return false;
    }

    public static function deleteAd(int $id): void {
        $db = DB::getInstance();
        $db->delete('advertisement_stats', ['advertisement_id', $id]);
        $db->delete('advertisements', ['id', $id]);
    }

    /**
     * Grant module permissions to the Admin group (id 2) when present.
     *
     * @param string[] $permissions
     */
    public static function grantPermissionsToAdminGroup(array $permissions): void {
        $group = Group::find('2');
        if ($group === null) {
            return;
        }

        $perms = json_decode($group->permissions ?? '{}', true);
        if (!is_array($perms)) {
            $perms = [];
        }

        foreach ($permissions as $permission) {
            $perms[$permission] = 1;
        }

        DB::getInstance()->update('groups', $group->id, [
            'permissions' => json_encode($perms),
        ]);
    }

    public static function ensureWidgetLocation(string $name, string $location): void {
        $db = DB::getInstance();
        $row = $db->get('widgets', ['name', $name])->first();
        if (!$row) {
            return;
        }

        if ($row->location !== $location) {
            $db->update('widgets', $row->id, [
                'location' => $location,
            ]);
        }
    }
}
