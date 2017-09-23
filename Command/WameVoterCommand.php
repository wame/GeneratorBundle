<?php
declare(strict_types=1);

namespace Wame\GeneratorBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Wame\GeneratorBundle\Generator\WameVoterGenerator;

class WameVoterCommand extends ContainerAwareCommand
{
    use WameCommandTrait;

    protected function configure()
    {
        $this
            ->setName('wame:generate:voter')
            ->setDescription('Generates a Voter based on a Doctrine entity')
            ->addArgument('entity', InputArgument::REQUIRED, 'The entity class name to initialize (shortcut notation)')
            ->addOption('overwrite', null, InputOption::VALUE_NONE, 'Overwrite file if already exists')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->initializeBaseSettings($input, $output);
        if ($this->enableVoters === false) {
            throw new DisabledException('The configuration \'wame_generator.enable_voters\' is set to false. Remove this setting from your config.yml if you wish to generate voters.');
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $crudQuestionHelper = $this->getCrudQuestionHelper();

        $metaEntity = $this->getMetaEntityFormInput($input);
        $forceOverwrite = $input->getOption('overwrite');

        $crudQuestionHelper->writeSection($output, 'Voter generation');
        if ($this->getVoterGenerator()->generateByMetaEntity($metaEntity, $forceOverwrite) === false) {
            $output->writeln('');
            $output->writeln('  To overwrite file, use the --overwrite option');
        }
        $output->writeln('');
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $crudQuestionHelper = $this->getCrudQuestionHelper();

        if (!$input->hasArgument('entity') || !$input->getArgument('entity')) {
            $crudQuestionHelper->askEntityName($input, $output, $this->defaultBundle);
        }
    }

    protected function getVoterGenerator(): WameVoterGenerator
    {
        return $this->getContainer()->get(WameVoterGenerator::class);
    }
}
