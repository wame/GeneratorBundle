<?php
declare(strict_types=1);

namespace Wame\SensioGeneratorBundle\Command\Helper;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Wame\SensioGeneratorBundle\Inflector\Inflector;

class CrudQuestionHelper extends QuestionHelper
{
    public function askRoutePrefix(InputInterface $input, OutputInterface $output, $entity)
    {
        // route prefix
        $prefix = $input->getOption('route-prefix') ?: Inflector::tableize($entity);

        $output->writeln(array(
            '',
            'Determine the routes prefix (all the routes will be "mounted" under this',
            'prefix: /prefix/, /prefix/new, ...).',
            '',
        ));
        $prefix = $this->ask($input, $output, new Question($this->getQuestion('Routes prefix', '/'.$prefix), '/'.$prefix));
        $input->setOption('route-prefix', $prefix);
    }

    public function askWithWrite(InputInterface $input, OutputInterface $output)
    {
        $withWrite = $input->getOption('with-write') ?: true;
        $output->writeln(array(
            '',
            'By default, the generator creates all actions.',
            'You can also ask it to generate only index and show.',
            '',
        ));
        $question = new ConfirmationQuestion($this->getQuestion('Do you want to generate the "write" actions', $withWrite ? 'yes' : 'no', '?'), $withWrite);

        $withWrite = $this->ask($input, $output, $question);
        $input->setOption('with-write', $withWrite);
    }

    public function askWithDatatable(InputInterface $input, OutputInterface $output)
    {
        $withDatatable = $input->getOption('with-datatable') ?: true;
        $question = new ConfirmationQuestion(
            $this->getQuestion('Do you want to use a datatable?', $withDatatable ? 'yes' : 'no', '?'),
            $withDatatable
        );
        $withDatatable = $this->ask($input, $output, $question);
        $input->setOption('with-datatable', $withDatatable);
    }

    public function askWithVoter(InputInterface $input, OutputInterface $output)
    {
        $withVoter = $input->getOption('with-voter') ?: true;
        $question = new ConfirmationQuestion(
            $this->getQuestion('Do you want to generate a voter?', $withVoter ? 'yes' : 'no', '?'),
            $withVoter
        );
        $withVoter = $this->ask($input, $output, $question);
        $input->setOption('with-voter', $withVoter);
    }

}
