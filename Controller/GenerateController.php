<?php

namespace Wame\SensioGeneratorBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Wame\SensioGeneratorBundle\Form\EntityType;
use Wame\SensioGeneratorBundle\Generator\WameEntityGenerator;
use Wame\SensioGeneratorBundle\Inflector\Inflector;
use Wame\SensioGeneratorBundle\MetaData\MetaEntity;

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
            $entityGenerator = new WameEntityGenerator();
            $path = $entityGenerator->generateByMetaEntity($metaEntity)->getEntityPath();
            $this->addFlash('success', sprintf('Entity %s generated in %s', $metaEntity->getEntityName(), $path));
        }

        return $this->render('@WameSensioGenerator/entity.html.twig', [
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
