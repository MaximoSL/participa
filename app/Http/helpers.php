<?php

if (!function_exists('asset_url')) {
    /**
     * Generate a asset_url for the application.
     *
     * @param string $path
     * @param mixed  $parameters
     * @param bool   $secure
     *
     * @return string
     */
    function asset_url($path = null, $parameters = [], $secure = null)
    {
        if ($assetsPath = config('app.assets_url')) {
            $path = $assetsPath.'/'.$path;
        }

        return app('Illuminate\Contracts\Routing\UrlGenerator')->to($path, $parameters, $secure);
    }
}
