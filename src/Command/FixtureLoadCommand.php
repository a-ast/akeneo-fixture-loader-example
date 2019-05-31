<?php

namespace App\Command;

use Aa\AkeneoDataLoader\Exception\LoaderValidationException;
use Aa\AkeneoFixtureLoader\FixtureLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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

            'product_{1..3}' => [
                'identifier' => 'test-'.bin2hex(openssl_random_pseudo_bytes(10)),
            ]

        ];


        $this->loader->load($fixtures);
    }

    protected function outputException(LoaderValidationException $e, OutputInterface $output)
    {
        $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

        foreach ($e->getValidationErrors() as $error) {
            $output->writeln(
                sprintf(
                    '<error>%s: %s</error>',
                    $error['code'] ?? '',
                    $error['message'] ?? ''
                )
            );
        }
    }
}
