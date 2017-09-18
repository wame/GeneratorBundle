<?php
declare(strict_types=1);

namespace Wame\SensioGeneratorBundle\Command;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Wame\SensioGeneratorBundle\Generator\WameDatatableGenerator;
use Wame\SensioGeneratorBundle\Generator\WameVoterGenerator;
use Wame\SensioGeneratorBundle\MetaData\MetaEntity;
use Wame\SensioGeneratorBundle\MetaData\MetaEntityFactory;

/**
 * Generates a CRUD for a Doctrine entity.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class WameCrudCommand extends GenerateDoctrineCrudCommand
{
    use WameCommandTrait;

    /** @var  WameDatatableGenerator */
    protected $datatableGenerator;
    /** @var  WameVoterGenerator */
    protected $voterGenerator;

    protected function configure()
    {
        parent::configure();
        $this
            ->setName('wame:generate:crud')
            ->addOption('with-datatable', null, InputOption::VALUE_NONE, 'Whether or not to generate a datatable')
            ->addOption('with-voter', null, InputOption::VALUE_NONE, 'Whether or not to generate a voter')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $metaEntity = $this->getMetaEntity($input);

        if ($input->getOption('with-datatable')) {
            $this->getDatatableGenerator()->generate($metaEntity);
        }
        if ($input->getOption('with-voter')) {
            $this->getVoterGenerator()->generateByMetaEntity($metaEntity);
        }
    }

    protected function getMetaEntity(InputInterface $input) : MetaEntity
    {
        $entity = $input->getArgument('entity');
        list($bundle, $entity) = $this->parseShortcutNotation($entity);
        $bundle = $this->getContainer()->get('kernel')->getBundle($bundle);

        $doctrine = $this->getContainer()->get("doctrine");
        $em = $doctrine->getManager();

        $entityClassname = $bundle->getNamespace().'\\Entity\\'.$entity;
        $classMetaData = $em->getClassMetadata($entityClassname);

        return MetaEntityFactory::createFromClassMetadata($classMetaData, $bundle);
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        parent::interact($input, $output);

        $questionHelper = $this->getQuestionHelper();

        $withDatatable = $input->getOption('with-datatable') ?: true;
        $question = new ConfirmationQuestion(
            $questionHelper->getQuestion('Do you want to use a datatable?', $withDatatable ? 'yes' : 'no', '?'),
            $withDatatable
        );
        $withDatatable = $questionHelper->ask($input, $output, $question);
        $input->setOption('with-datatable', $withDatatable);

        $withVoter = $input->getOption('with-voter') ?: true;
        $question = new ConfirmationQuestion(
            $questionHelper->getQuestion('Do you want to generate a voter?', $withVoter ? 'yes' : 'no', '?'),
            $withVoter
        );
        $withVoter = $questionHelper->ask($input, $output, $question);
        $input->setOption('with-voter', $withVoter);
    }

    protected function getDatatableGenerator()
    {
        if (null === $this->datatableGenerator) {
            $this->datatableGenerator = $this->getContainer()->get(WameDatatableGenerator::class);
        }

        return $this->datatableGenerator;
    }

    protected function getVoterGenerator()
    {
        if (null === $this->voterGenerator) {
            $this->voterGenerator = $this->getContainer()->get(WameVoterGenerator::class);
        }

        return $this->voterGenerator;
    }
}
