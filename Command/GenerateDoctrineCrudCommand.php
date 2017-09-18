<?php
declare(strict_types=1);

namespace Wame\SensioGeneratorBundle\Command;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Wame\SensioGeneratorBundle\Command\AutoComplete\EntitiesAutoCompleter;
use Wame\SensioGeneratorBundle\Generator\DoctrineCrudGenerator;
use Wame\SensioGeneratorBundle\Generator\WameFormGenerator;

/**
 * Generates a CRUD for a Doctrine entity.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class GenerateDoctrineCrudCommand extends GenerateDoctrineCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('wame:doctrine:generate:crud')
            ->setDescription('Generates a CRUD based on a Doctrine entity')
            ->addArgument('entity', InputArgument::OPTIONAL, 'The entity class name to initialize (shortcut notation)')
            ->addOption('entity', null, InputOption::VALUE_OPTIONAL, 'The entity class name to initialize (shortcut notation)')
            ->addOption('route-prefix', null, InputOption::VALUE_REQUIRED, 'The route prefix')
            ->addOption('with-write', null, InputOption::VALUE_NONE, 'Whether or not to generate create, new and delete actions')
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'The format used for configuration files (php, xml, yml, or annotation)', 'annotation')
            ->addOption('overwrite', null, InputOption::VALUE_NONE, 'Overwrite any existing controller or form class when generating the CRUD contents')
            ->setHelp(<<<EOT
The <info>%command.name%</info> command generates a CRUD based on a Doctrine entity.

The default command only generates the list and show actions.

<info>php %command.full_name% AcmeBlogBundle:Post --route-prefix=post_admin</info>

Using the --with-write option allows to generate the new, edit and delete actions.

<info>php %command.full_name% AcmeBlogBundle:Post --route-prefix=post_admin --with-write</info>

Every generated file is based on a template. There are default templates but they can be overridden by placing custom templates in one of the following locations, by order of priority:

<info>BUNDLE_PATH/Resources/SensioGeneratorBundle/skeleton/crud
APP_PATH/Resources/SensioGeneratorBundle/skeleton/crud</info>

And

<info>__bundle_path__/Resources/SensioGeneratorBundle/skeleton/form
__project_root__/app/Resources/SensioGeneratorBundle/skeleton/form</info>

You can check https://github.com/sensio/SensioGeneratorBundle/tree/master/Resources/skeleton
in order to know the file structure of the skeleton
EOT
            )
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();

        if ($input->isInteractive()) {
            $question = new ConfirmationQuestion($questionHelper->getQuestion('Do you confirm generation', 'yes', '?'), true);
            if (!$questionHelper->ask($input, $output, $question)) {
                $output->writeln('<error>Command aborted</error>');

                return 1;
            }
        } else {
            // BC to be removed in 4.0
            if ($input->hasOption('entity') && $entityOption = $input->getOption('entity')) {
                @trigger_error('Using the "--entity" option has been deprecated since version 3.0 and will be removed in 4.0. Pass it as argument instead.', E_USER_DEPRECATED);

                $input->setArgument('entity', $entityOption);
            }
        }

        $entity = WameValidators::validateEntityName($input->getArgument('entity'));
        list($bundle, $entity) = $this->parseShortcutNotation($entity);

        $format = WameValidators::validateFormat($input->getOption('format'));
        $prefix = $this->getRoutePrefix($input, $entity);
        $withWrite = $input->getOption('with-write');
        $forceOverwrite = $input->getOption('overwrite');
        $withDatatable = $input->hasOption('with-datatable') ? $input->getOption('with-datatable') : false;
        $withVoter = $input->hasOption('with-voter') ? $input->getOption('with-voter') : false;

        $questionHelper->writeSection($output, 'CRUD generation');

        try {
            $entityClass = $this->getContainer()->get('doctrine')->getAliasNamespace($bundle).'\\'.$entity;
            $metadata = $this->getEntityMetadata($entityClass);
        } catch (\Exception $e) {
            throw new \RuntimeException(sprintf('Entity "%s" does not exist in the "%s" bundle. Create it with the "doctrine:generate:entity" command and then execute this command again.', $entity, $bundle));
        }

        $bundle = $this->getContainer()->get('kernel')->getBundle($bundle);

        $generator = $this->getGenerator($bundle);
        $generator->generate($bundle, $entity, $metadata[0], $format, $prefix, $withWrite, $forceOverwrite, $withDatatable, $withVoter);

        $output->writeln('Generating the CRUD code: <info>OK</info>');

        $errors = array();
        $runner = $questionHelper->getRunner($output, $errors);

        // form
        if ($withWrite) {
            $this->generateForm($bundle, $entity, $metadata, $forceOverwrite);
            $output->writeln('Generating the Form code: <info>OK</info>');
        }

        $questionHelper->writeGeneratorSummary($output, $errors);
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();
        $questionHelper->writeSection($output, 'Welcome to the Doctrine2 CRUD generator');

        // namespace
        $output->writeln(array(
            '',
            'This command helps you generate CRUD controllers and templates.',
            '',
            'First, give the name of the existing entity for which you want to generate a CRUD',
            '(use the shortcut notation like <comment>AcmeBlogBundle:Post</comment>)',
            '',
        ));

        if ($input->hasOption('entity') && $entityOption = $input->getOption('entity')) {
            @trigger_error('Using the "--entity" option has been deprecated since version 3.0 and will be removed in 4.0. Pass it as argument instead.', E_USER_DEPRECATED);

            $input->setArgument('entity', $entityOption);
        }

        $question = new Question($questionHelper->getQuestion('The Entity shortcut name', $input->getArgument('entity')), $input->getArgument('entity'));
        $question->setValidator(array('Wame\SensioGeneratorBundle\Command\WameValidators', 'validateEntityName'));

        $autocompleter = new EntitiesAutoCompleter($this->getContainer()->get('doctrine')->getManager());
        $autocompleteEntities = $autocompleter->getSuggestions();
        $question->setAutocompleterValues($autocompleteEntities);
        $entity = $questionHelper->ask($input, $output, $question);

        $input->setArgument('entity', $entity);
        list($bundle, $entity) = $this->parseShortcutNotation($entity);

        try {
            $entityClass = $this->getContainer()->get('doctrine')->getAliasNamespace($bundle).'\\'.$entity;
            $metadata = $this->getEntityMetadata($entityClass);
        } catch (\Exception $e) {
            throw new \RuntimeException(sprintf('Entity "%s" does not exist in the "%s" bundle. You may have mistyped the bundle name or maybe the entity doesn\'t exist yet (create it first with the "doctrine:generate:entity" command).', $entity, $bundle));
        }

        // write?
        $withWrite = $input->getOption('with-write') ?: true;
        $output->writeln(array(
            '',
            'By default, the generator creates all actions.',
            'You can also ask it to generate only index and show.',
            '',
        ));
        $question = new ConfirmationQuestion($questionHelper->getQuestion('Do you want to generate the "write" actions', $withWrite ? 'yes' : 'no', '?', $withWrite), $withWrite);

        $withWrite = $questionHelper->ask($input, $output, $question);
        $input->setOption('with-write', $withWrite);

        // format
        $format = $input->getOption('format');
        $output->writeln(array(
            '',
            'Determine the format to use for the generated CRUD.',
            '',
        ));
        $question = new Question($questionHelper->getQuestion('Configuration format (yml, xml, php, or annotation)', $format), $format);
        $question->setValidator(array('Wame\SensioGeneratorBundle\Command\WameValidators', 'validateFormat'));
        $format = $questionHelper->ask($input, $output, $question);
        $input->setOption('format', $format);

        // route prefix
        $prefix = $this->getRoutePrefix($input, $entity);
        $output->writeln(array(
            '',
            'Determine the routes prefix (all the routes will be "mounted" under this',
            'prefix: /prefix/, /prefix/new, ...).',
            '',
        ));
        $prefix = $questionHelper->ask($input, $output, new Question($questionHelper->getQuestion('Routes prefix', '/'.$prefix), '/'.$prefix));
        $input->setOption('route-prefix', $prefix);

        // summary
        $output->writeln(array(
            '',
            $this->getHelper('formatter')->formatBlock('Summary before generation', 'bg=blue;fg=white', true),
            '',
            sprintf('You are going to generate a CRUD controller for "<info>%s:%s</info>"', $bundle, $entity),
            sprintf('using the "<info>%s</info>" format.', $format),
            '',
        ));
    }

    /**
     * Tries to generate forms if they don't exist yet and if we need write operations on entities.
     */
    protected function generateForm($bundle, $entity, $metadata, $forceOverwrite = false)
    {
        /** @var WameFormGenerator $formGenerator */
        $formGenerator = $this->getContainer()->get(WameFormGenerator::class);
        $formGenerator->generateByBundleAndEntityName($bundle, $entity);
//        $this->getFormGenerator($bundle)->generate($bundle, $entity, $metadata[0], $forceOverwrite);
    }

    protected function getRoutePrefix(InputInterface $input, $entity)
    {
        $prefix = $input->getOption('route-prefix') ?: strtolower(str_replace(array('\\', '/'), '_', $entity));

        if ($prefix && '/' === $prefix[0]) {
            $prefix = substr($prefix, 1);
        }

        return $prefix;
    }

    protected function createGenerator($bundle = null)
    {
        return new DoctrineCrudGenerator(
            $this->getContainer()->getParameter('kernel.root_dir')
        );
    }
}
