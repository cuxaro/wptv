<?php

/*
Plugin Name: WPTV Shortcode
Plugin URI: https://ivanbarreda.com/wptv-shortcode
Description: Muestra un listado de videos de WordPress.TV con la palabra clave
Author: Ivan Barreda
Version: 0.9.8

*/

/*
Guide Hooks Filter:

wptv_format_date
by default 'Y-m-d'

wptv_format_title
*/

/*
wp_dequeue_style('wptv-default_style'); 
Use this hook inside functions.php to use the plugin without customization
*/

/*
[wptv language="spanishespanol" keyword="woocommerce"]
All videos with keyword "woocommerce" with Spanish Language and 10 videos

[wptv language="english" " keyword="paypal" posts_per_page="100"]
All videos with keyword "paypal" with English Language and maxium 100 videos

[wptv keyword="stripe"]
*/


function wptv_func($attr)
{

    $attr = shortcode_atts([
        'language' => '',
        'keyword' => '',
        'posts_per_page' => '10'
    ], $attr);

    $url = 'https://wordpress.tv/api/videos.json?s=' . urlencode($attr['keyword']) . '&language=' . urlencode($attr['language']) . '&posts_per_page=' . urlencode($attr['posts_per_page']);
    $response = wp_remote_get($url, []);

    if (is_wp_error($response) || !isset($response['body'])) :
        return '';
    endif;

    $videos = json_decode($response['body'])->videos;

    $return = "<div class='wptv_container'>";

    if ($videos) :

        foreach ($videos as $video) :

            $format_date = 'Y-m-d';
            if (has_filter('wptv_format_date')) :
                $format_date = apply_filters('wptv_format_date', $format_date);
            endif;

            $video_title = $video->title;
            if (has_filter('wptv_format_title')) :
                $video_title = apply_filters('wptv_format_title', $video_title);
            endif;

            $return .= "<article class='wptv_item' >";
            $return .= "<p class='wptv_item__title'>" . $video_title . "</p>";
            $return .= "<video class='wptv_item__video' src='" . $video->video->mp4->high . "' controls='controls' preload='none' poster='" . $video->thumbnail . "'></video>";
            $return .= "<a class='wptv_item__title' target='_blank' href='" . $video->permalink . "' >" . __('Enlace Original', 'wptv') . "</a>";
            $return .= "<span class='wptv_item__date'>" . __('publicado el: ', 'wptv') . date($format_date, strtotime($video->date)) . "</span>";
            $return .= "</article>";

        endforeach;
    else :

        $return .= "<p class='wptv_novideos'>" . __('No hay videos disponibles con esos terminos', 'wptv') . "</p>";

    endif;
    $return .= "</div>";

    return $return;
}
add_shortcode('wptv', 'wptv_func');


function wptv_enqueue_default_style()
{
    wp_enqueue_style('wptv-default_style', plugins_url('/css/wptv.css', __FILE__));

    // Move this line inside your function.php to dequeue the style
    // wp_dequeue_style('wptv-default_style');
}
add_action('wp_enqueue_scripts', 'wptv_enqueue_default_style');
