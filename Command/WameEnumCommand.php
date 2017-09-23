<?php
declare(strict_types=1);

namespace Wame\GeneratorBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Yaml;
use Wame\GeneratorBundle\Command\Helper\QuestionHelper;
use Wame\GeneratorBundle\Generator\WameEnumGenerator;
use Wame\GeneratorBundle\Inflector\Inflector;

class WameEnumCommand extends ContainerAwareCommand
{
    use WameCommandTrait;

    protected function configure()
    {
        $this
            ->setName('wame:generate:enum')
            ->setDescription('Generates a Form based on a Doctrine entity')
            ->addArgument('enum', InputArgument::REQUIRED, 'The enum class name to initialize (shortcut notation)')
            ->addOption('overwrite', null, InputOption::VALUE_NONE, 'Overwrite file if already exists')
            ->addOption('options', null, InputOption::VALUE_REQUIRED, 'enum options (for each option, use format: option-value:CONTANTNAME:display-value')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->initializeBaseSettings($input, $output);

        if (!$input->hasArgument('enum') || !$input->getArgument('enum')) {
            return;
        }

        $entity = $input->getArgument('enum');

        if ($this->defaultBundle !== null && strpos($entity, ':') === false) {
            $input->setArgument('enum', $this->defaultBundle.':'.$entity);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $questionHelper = $this->getQuestionHelper();

        $forceOverwrite = $input->getOption('overwrite') ?? false;

        $enumOptions = $this->parseEnumOptions($input->getOption('options'));

        list($bundle, $enum) = $this->parseShortcutNotation($input->getArgument('enum'));
        $bundle = $this->getContainer()->get('kernel')->getBundle($bundle);

        $questionHelper->writeSection($output, 'ENUM generation');

        if ($this->getEnumGenerator()->generate($bundle, $enum, $enumOptions, $forceOverwrite)=== false) {
            $output->writeln('');
            $output->writeln('  To overwrite file, use the --overwrite option');
        } else {
            $this->enableDBALType($input, $output);
        }
        $output->writeln('');
    }

    protected function getQuestionHelper(): QuestionHelper
    {
        $question = $this->getHelperSet()->get('question');
        if (!$question || (new \ReflectionClass($question))->getName() !== (new \ReflectionClass(QuestionHelper::class))->getName()) {
            $this->getHelperSet()->set($question = new QuestionHelper());
        }
        return $question;
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $questionHelper = $this->getQuestionHelper();

        if (!$input->hasArgument('enum') || !$input->getArgument('enum')) {
            $question = new Question($questionHelper->getQuestion('Enum name, should end with Type', ''));
            $question->setValidator(WameValidators::getEnumNameValidator($this->defaultBundle));
            $question->setAutocompleterValues($this->getContainer()->getParameter('kernel.bundles'));
            $enum = $questionHelper->ask($input, $output, $question);

            $input->setArgument('enum', $enum);
        }

        $enumOptions = $this->parseEnumOptions($input->getOption('options'));

        while (true) {
            $question = new Question($questionHelper->getQuestion('New option value [as persisted] (press <return> to stop adding values)', null), null);
            $optionValue = $questionHelper->ask($input, $output, $question);
            if (!$optionValue) {
                break;
            }
            $constantDefault = Inflector::constantize($optionValue);
            $question = new Question($questionHelper->getQuestion('Constant for this option', $constantDefault), $constantDefault);
            $constantValue = $questionHelper->ask($input, $output, $question);


            $displayDefault = Inflector::humanize($optionValue);
            $question = new Question($questionHelper->getQuestion('Constant for this option', $displayDefault), $displayDefault);
            $displayValue = $questionHelper->ask($input, $output, $question);

            $enumOptions[] = [$constantDefault, $constantValue, $displayValue];
        }
        $input->setOption('options', $enumOptions);
    }

    /**
     * Copy of parseFields in Sensio\Bundle\GeneratorBundle\Command\GenerateDoctrineEntityCommand
     */
    protected function parseEnumOptions($enumOptions): array
    {
        $enumOptions = $enumOptions ?: [];
        if (is_array($enumOptions)) {
            return $enumOptions;
        }
        $enumOptionsAsArray = [];

        //enable using array format and semilocons
        $enumOptions = str_replace(["\r\n","\n",'],[','], [',';','[',']',', '], ['','','|', '',',','','',','], $enumOptions);
        $enumOptionSets = explode('|', $enumOptions);

        foreach ($enumOptionSets as $enumOptionSet) {
            $enumOptionParts = explode(',', $enumOptionSet);
            $numberOfParts = count($enumOptionParts);
            if ($numberOfParts < 1 || $numberOfParts > 3) {
                throw new \InvalidArgumentException("The providion option '--options' has invalid content");
            }

            $enumOptionsAsArray[] = [
                $enumOptionParts[0],
                $enumOptionParts[1] ?? Inflector::constantize($enumOptionParts[0]),
                $enumOptionParts[2] ?? Inflector::humanize($enumOptionParts[0]),
            ];
        }
        return $enumOptionsAsArray;
    }


    protected function enableDBALType(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();

        list($bundle, $enumName) = $this->parseShortcutNotation($input->getArgument('enum'));
        $bundle = $this->getContainer()->get('kernel')->getBundle($bundle);

        $class = $bundle->getNamespace().'\\DBAL\\Types\\'.$enumName;

        if ($input->isInteractive()) {
            $question = new ConfirmationQuestion($questionHelper->getQuestion('Confirm automatic update of the config', 'yes', '?'), true);
            if (!$questionHelper->ask($input, $output, $question)) {
                $questionHelper->writeSection($output, sprintf('Add the Enum Type to the Doctrine DBAL config:        <comment>%s: %s</comment>\n', $enumName, $class));
            }
        }

        $output->write('Registering the doctrine enum type: ');

        $configFile = $this->getContainer()->getParameter('kernel.root_dir').'/config/config.yml';
        $config = file_get_contents($configFile);

        $configYaml = Yaml::parse($config);
        if (!isset($configYaml['doctrine'])) {
            $output->writeln('\n<error>Could not automatically register type (the doctrine config could not be found)</error>');
            return;
        }
        if (isset($configYaml['doctrine']['dbal'], $configYaml['doctrine']['dbal']['types'], $configYaml['doctrine']['dbal']['types'][$enumName])) {
            $output->writeln(sprintf("\n<error>The enum '%s' is already defined</error>", $enumName));
            return;
        }

        if (preg_match('/doctrine:(\s+dbal:(\s+types:)?)?/', $config, $matches)) {
            $enumConfig = sprintf("\n            %s: %s", $enumName, $class);
            if (!isset($matches[2])) {
                $enumConfig = "\n        types:" . $enumConfig . "\n";
            }
            if (!isset($matches[1])) {
                $enumConfig = "\n    dbal:" . $enumConfig;
            }

            $config = str_replace($matches[0], $matches[0] . $enumConfig, $config);
            file_put_contents($configFile, $config);
            $output->writeln('<info>done</info>');
        }
    }

    protected function getEnumGenerator(): WameEnumGenerator
    {
        return $this->getContainer()->get(WameEnumGenerator::class);
    }
}
