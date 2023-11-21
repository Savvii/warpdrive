<?php

namespace Savvii;

/**
 * Class SavviiCacheWPRocket
 * Trigger WP Rockets plugin
 */
class CacheFlusherWPRocket implements CacheFlusherInterface {

    const CACHENAME='WP Rocket';

    /**
     * Are we in a test
     *
     * @var bool
     */
    protected $inTest = false;

    /**
     * Return value of flush_opcache() when overridden
     * @var bool
     */
    protected $inTestResult = true;

    /**
     * Return value of is_enabled() when overridden
     * @var bool
     */
    protected $inTestEnabled = true;


    /**
     * Flush cache
     * @return bool True on success
     */
    public function flush() {
        // early exit when in phpunittest
        if ($this->inTest) return $this->inTestResult;

        // Early return if not enabled
        if (!$this->is_enabled()) return true;

        $language = get_bloginfo('language');

        // clean base domain
        if (function_exists('rocket_clean_domain')) {
            rocket_clean_domain($language);
        }

        // clean minified CSS and JS
        if (function_exists('rocket_clean_minify')) {
            rocket_clean_minify($language);
        }

        return true;
    }

    /**
     * Flush cache for a specific domain
     * @param null $domain
     * @return bool True on success
     */
    public function flush_domain($domain = null) {
        // early exit when in phpunittest
        if ($this->inTest) return $this->inTestEnabled;

        // Early return if not enabled
        if (!$this->is_enabled()) return true;

        // Early return if no domain specified
        if (is_null($domain) || empty($domain)) {
            return true;
        }

        if (function_exists('rocket_clean_files')) {
            $paths = [
                'https://' . $domain . '/',
                'http://' . $domain . '/'
            ];
            rocket_clean_files($paths);
        }

        return true;
    }

    /**
     * Check if the WP Rocket plugin is enabled
     *
     * @return bool
     */
    public function is_enabled()
    {
        // early exit when in phpunittest
        if ($this->inTest) return $this->inTestEnabled;

        // See if the plugin exists / is activated by checking if its functions exists
        return function_exists('get_rocket_cdn_url');
    }
}
