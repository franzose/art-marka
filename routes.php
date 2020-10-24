<?php

require_once __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/bootstrap.php';

$lang = 'ru';

if ($curlang = $session->get('current_language')){
    $lang = $curlang;
}

foreach(glob(__DIR__ . '/languages/' . $lang . '/*.yml') as $locale){
    $translator->addResource('yaml', $locale, $lang);
}

$translator->setLocale($lang);

// текущий uri и языки
$app->before(function($request) use($twig, $lang) {
    $twig->addGlobal('current_uri', $request->getRequestUri());

    foreach(glob(__DIR__ . '/languages/*') as $locale) {
        $languages[] = basename($locale);
    }

    $twig->addGlobal('current_lang', $lang);
});

// устанавливает текущий язык
$app->get('/lang/{lang}', function($lang) use($app, $session){
    if (is_dir(__DIR__ . '/languages/' . $lang)){
        $session->set('current_language', $lang);
    }

    return $app->redirect($_SERVER['HTTP_REFERER']);
});

// главная страница
$app->get('/', function() use($twig){
    return $twig->render('index.twig');
});

// все фотоальбомы
$app->get('/albums', function() use($twig){
    return $twig->render('albums.twig', array(
        'albums' => getAlbumsList()
    ));
});

// отдельный фотоальбом
$app->get('/album/{slug}', function($slug) use($twig){
    list($slug, $name, $images) = getAlbum($slug);

    return $twig->render('album.twig', array(
        'slug'   => $slug,
        'name'   => $name,
        'images' => $images
    ));
});

// общий роут для страниц
$app->match('{url}', function($url) use($twig){
    $view = $url.'.twig';

    if ( ! file_exists(realpath('../views/'.$view))){
        throw new \Symfony\Component\HttpKernel\Exception\HttpException(404);
    }

    return $twig->render($view);

})->assert('url', '.+');

// роут для ошибок типа 404, 500
$app->error(function($exception, $code) use($twig){
    return $twig->render($code.'.twig');
});

$app->run();