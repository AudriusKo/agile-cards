<?php
/**
 * app.php
 *
 * @filesource
 * @created 2014-04-03
 */

require_once __DIR__ . '/../vendor/autoload.php';

$app          = new Silex\Application();
$app['debug'] = true;

$app->register(
    new Silex\Provider\TwigServiceProvider(),
    [
        'twig.path' => __DIR__ . '/views',
    ]
);

$app->get(
    '/',
    function () use ($app) {
        $dir   = __DIR__ . '/data/';
        $files = [];

        if ($handle = opendir($dir)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    $file_parts = pathinfo($entry);

                    if ('xml' == strtolower($file_parts['extension'])) {
                        $files[] = $entry;
                    }
                }
            }
            closedir($handle);
        }

        return $app['twig']->render('files.twig', ['files' => $files]);
    }
);

$app->get(
    '/cards/{file}',
    function ($file) use ($app) {

        $file       = __DIR__ . '/data/' . $file;
        $file_parts = pathinfo($file);

        if ('xml' != strtolower($file_parts['extension']) || !file_exists($file)) {
            return "Wrong file type or file not found!";
        }

        $cards = [];
        $xml   = simplexml_load_file($file);

        if ($xml instanceof SimpleXMLElement) {
            foreach ($xml->channel->item as $card) {
                switch ($card->type['id']) {
                    case 1:
                    case 9:
                        $icon = 'bug';
                        break;
                    case 19:
                        $icon = 'folder';
                        break;
                    default:
                        $icon = 'wrench';
                    }

                $id = (string) $card->key;
                $cards[$id] = [
                    'id'          => $id,
                    'parent'      => (string)$card->parent,
                    'key'         => (string)$card->key,
                    'type'        => (string)$card->type,
                    'summary'     => (string)$card->summary,
                    'description' => (string)$card->description,
                    'priority'    => (string)$card->priority,
                    'status'      => (string)$card->status,
                    'resolution'  => (string)$card->resolution,
                    'assignee'    => (string)$card->assignee,
                    'reporter'    => (string)$card->reporter,
                    'estimate'    => (string)$card->timeestimate,
                    'timespent'   => (string)$card->aggregatetimespent,
                    'icon'        => $icon

                ];
            }
        }

        return $app['twig']->render('cards.twig', ['cards' => $cards]);
    }
);
