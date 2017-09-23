<?php
declare(strict_types=1);

namespace Wame\GeneratorBundle\Controller;

use Doctrine\Bundle\DoctrineBundle\Mapping\DisconnectedMetadataFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Wame\GeneratorBundle\Form\EntityType;
use Wame\GeneratorBundle\Generator\DoctrineCrudGenerator;
use Wame\GeneratorBundle\Generator\WameDatatableGenerator;
use Wame\GeneratorBundle\Generator\WameEntityGenerator;
use Wame\GeneratorBundle\Generator\WameVoterGenerator;
use Wame\GeneratorBundle\Inflector\Inflector;
use Wame\GeneratorBundle\MetaData\MetaEntity;

class GenerateController extends Controller
{
    /**
     * @Route("generator/entity", name="generate-entity")
     */
    public function entityAction(Request $request)
    {
        $metaEntity = new MetaEntity();
        $form = $this->createForm(EntityType::class, $metaEntity);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $generateRepo = $form->get('generate_repository')->getData();
            $generateForm = $form->get('generate_form')->getData();
            $generateDatatable = $form->get('generate_datatable')->getData();
            $generateCrud = $form->get('generate_crud')->getData();
            $generateVoter = $form->get('generate_voter')->getData();

            $entityGenerator = new WameEntityGenerator();
            $path = $entityGenerator->generateByMetaEntity($metaEntity, $generateRepo)->getEntityPath();
            $this->addFlash('success', sprintf('Entity %s generated in %s', $metaEntity->getEntityName(), $path));

            if ($generateVoter) {
                $voterGenerator = new WameVoterGenerator();
                $voterGenerator->generate($metaEntity);
            }
            if ($generateDatatable) {
                $datatableGenerator = new WameDatatableGenerator();
                $datatableGenerator->generate($metaEntity);
            }
            if ($generateCrud) {
                $crudGenerator = new DoctrineCrudGenerator(
                    $this->get('filesystem'),
                    $this->getParameter('kernel.root_dir')
                );
                $factory = new DisconnectedMetadataFactory($this->get('doctrine'));
                $metadata = $factory->getClassMetadata($metaEntity->getBundleNamespace().'\\Entity\\'.$metaEntity->getEntityName(), $path)->getMetadata();
                $crudGenerator->generate(
                    $metaEntity->getBundle(),
                    $metaEntity->getEntityName(),
                    reset($metadata),
                    'annotation',
                    Inflector::tableize($metaEntity->getEntityName()),
                    true,
                    true
                );
            }
        }

        return $this->render('@WameGenerator/entity.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("generator/plural-tableize", name="generate_plural_tabalize")
     */
    public function pluralTableizeAction(Request $request)
    {
        $word = $request->get('word');
        return new JsonResponse(Inflector::pluralTableize($word));
    }
}
