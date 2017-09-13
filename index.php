<?php

require __DIR__.'/vendor/autoload.php';

use Silex\Application;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\LocaleServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\VarDumperServiceProvider;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\NotBlank;

$app = new Application();
$app['debug'] = true;

/**
 * Providers
 */
$app->register(new TwigServiceProvider(), [
    'twig.path' => __DIR__.'/templates',
    'twig.form.templates' => [
        'bootstrap_3_layout.html.twig',
    ],
]);
$app->register(new FormServiceProvider());
$app->register(new ValidatorServiceProvider());
$app->register(new LocaleServiceProvider());
$app->register(new TranslationServiceProvider());
$app->register(new VarDumperServiceProvider());

/**
 * Controllers
 */
$app->match('/', function (Application $app, Request $request) {

    $versions = require __DIR__.'/etc/versions.php';

    $builder = $app['form.factory']->createBuilder();
    $builder
        ->add('version', ChoiceType::class, [
            'label' => 'バージョン',
            'choices' => $versions,
            'placeholder' => false,
            'constraints' => [
                new NotBlank(),
            ],
        ])
        ->add('directory', TextType::class, [
            'label' => '展開するディレクトリ(空白の場合はカレントディレクトリに展開します)',
            'required' => false,
            'attr' => [
                'placeholder' => '/shop'
            ],
        ])
        ->add('submit', SubmitType::class, [
            'label' => 'ダウンロード',
        ]);

    $form = $builder->getForm();
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        return $app['twig']->render('complete.twig');
    }

    return $app['twig']->render('index.twig', [
        'form' => $form->createView(),
    ]);
});

$app->match('/complete', function (Application $app, Request $request) {
    return $app['twig']->render('complete.twig');
});

/**
 * Run.
 */
$app->run();