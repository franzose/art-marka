<?php

define('IMG_PATH', $_SERVER['DOCUMENT_ROOT'].'/public/resources/img');
define('IMG_SHORTPATH', '/public/resources/img');

use Silex\Provider\TwigServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Symfony\Component\Translation\Loader\YamlFileLoader;

$app = new Silex\Application;
$app->register(new TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));

$app->register(new SessionServiceProvider());
$app->register(new TranslationServiceProvider(), array('locale_fallback' => 'en'));

$app['translator'] = $app->share($app->extend('translator', function($translator, $app) {
    $translator->addLoader('yaml', new YamlFileLoader());

    return $translator;
}));

$twig = $app['twig'];
$session = $app['session'];
$translator = $app['translator'];

//$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

/**
 * Возвращает массив с именами файлов изображений из папки альбома.
 *
 * @param $slug
 * @return array
 */
function getAlbum($slug){
    $images = array();
    $directory = IMG_PATH.'/albums/'.$slug;

    if ($directory = opendir($directory)){
        while(($image = readdir($directory)) !== false){
            if (isValidImage($image, 'thumb.jpg')){
                $images[] = $image;
            }
        }
    }

    $albums = getAlbumsList();
    $name = $albums[$slug];

    return array($slug, $name, $images);
}

/**
 * Возвращает список всех альбомов
 *
 * @return array
 */
function getAlbumsList(){
    global $translator;

    return array(
        'faces'         => $translator->trans('album_title_1'),
        'girl-and'      => $translator->trans('album_title_2'),
        'in-the-middle' => $translator->trans('album_title_3'),
        'may'           => $translator->trans('album_title_4'),
        'pilot'         => $translator->trans('album_title_5'),
        'rain'          => $translator->trans('album_title_6'),
        'travel'        => $translator->trans('album_title_7'),
        'fun'           => $translator->trans('album_title_8')
    );
}

/**
 * Проверяет, является ли файл директорией.
 *
 * @param $dir
 * @return bool
 */
function isValidDir($dir){
    return (strpos($dir, '.') === false);
}

/**
 * Проверяет изображение на входящее условие.
 *
 * @param $image
 * @param array $exclude
 * @return bool
 */
function isValidImage($image, $exclude = array()){
    $not_excluded = (array_search($image, (array)$exclude) === false);
    $has_ext = (strpos($image, '.') !== 0);

    return ($not_excluded && $has_ext);
}

/**
 * Проверяет, является ли изображение миниатюрой.
 *
 * @param $image
 * @return bool
 */
function isThumbnail($image){
    return (strpos($image, '_thumb.') !== false);
}