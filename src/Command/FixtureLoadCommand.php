<?php

namespace App\Command;

use Aa\AkeneoDataLoader\Exception\LoaderValidationException;
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

            'product_{1..100}' => [
                'identifier' => '<ean13()>',
                'family' => 'toiletries',
                'values' => [
                    'name' => [[
                        'data' => '<text()>',
                        'locale' => 'en_GB',
                        'scope' => null,
                    ]],
                    'short_description' => [[
                        'data' => '<paragraph()>',
                        'locale' => 'en_GB',
                        'scope' => 'ecommerce',
                    ]],
                    'description' => [[
                        'data' => '<randomHtml()>',
                        'locale' => 'en_GB',
                        'scope' => null,
                    ]],
                ]
            ]

        ];

        $style = new SymfonyStyle($input, $output);
        $stopwatch = new Stopwatch();
        $event = $stopwatch->start('load');

        try {
            $this->loader->loadData($fixtures);
        } catch (LoaderValidationException $e) {
            $this->outputException($e, $style);
        }

        $stopwatch->stop('load');

        $style->table([], [

            ['Time', ($event->getDuration() / 1000). ' s'],
            ['Memory', ($event->getMemory() / (1024*1024)). ' MB'],

        ]);
    }

    private function outputException(LoaderValidationException $e, SymfonyStyle $style)
    {
        $messages = [$e->getMessage()];

        var_dump($e->getValidationErrors());

        foreach ($e->getValidationErrors() as $violation) {

            $messages[] = sprintf('%s: %s',$violation['code'] ?? '',$violation['message'] ?? '');

            foreach ($violation['errors'] ?? [] as $error) {
                $messages[] = sprintf(' - %s: %s',$error['property'] ?? '',$error['message'] ?? '');
            }
        }

        $style->block($messages, null, 'error');
    }
}
