<?php

namespace App\Command;

use Aa\AkeneoDataLoader\Exception\LoaderException;
use Aa\AkeneoFixtureLoader\FixtureLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Stopwatch\Stopwatch;

class FixtureLoadCommand extends Command
{
    /**
     * @var \Aa\AkeneoFixtureLoader\FixtureLoader
     */
    private $loader;

    public function __construct(FixtureLoader $loader)
    {
        parent::__construct();

        $this->loader = $loader;
    }

    protected function configure()
    {
        $this
            ->setName('akeneo:fixture:load')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fixtures = [

            'product_{1..10}' => [
                'identifier' => '<ean13()>',
//                'family' => 'toiletries',
                'values' => [
                    'name' => [[
                        'data' => '<text()>',
                        'locale' => null,
                        'scope' => null,
                    ]],
//                    'short_description' => [[
//                        'data' => '<paragraph()>',
//                        'locale' => 'en_GB',
//                        'scope' => 'ecommerce',
//                    ]],
                    'description' => [[
                        'data' => '<randomHtml()>',
                        'locale' => 'en_US',
                        'scope' => 'ecommerce',
                    ]],
                ]
            ]

        ];


//        $fixtures = [
//
//            'asset_{1..10}' => [
//                'code' => 'asset_<ean13()>',
//            ]
//
//        ];

        $style = new SymfonyStyle($input, $output);
        $stopwatch = new Stopwatch();
        $event = $stopwatch->start('load');

        try {
            $this->loader->loadData($fixtures);
        } catch (LoaderException $e) {
            $this->outputException($e, $style);
        }

        $stopwatch->stop('load');

        $style->table([], [

            ['Time', ($event->getDuration() / 1000). ' s'],
            ['Memory', ($event->getMemory() / (1024*1024)). ' MB'],

        ]);
    }

    private function outputException(LoaderException $e, SymfonyStyle $style)
    {
        $messages = [$e->getMessage()] + $e->getErrors();

        $style->block($messages, null, 'error');
    }
}
