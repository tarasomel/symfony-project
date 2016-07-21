<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Cv;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class DefaultController extends Controller
{

  public function indexAction(Request $request)
  {
    // just setup a fresh $cv object (remove the dummy data)
    $cv = new Cv();

    $form = $this->createFormBuilder($cv)
            ->add('age', RangeType::class, array(
                'constraints' => new \Symfony\Component\Validator\Constraints\Range(
                        array(
                    'min' => 17,
                    'max' => 65,
                        )
                )
                    )
            )
            ->add('name', TextType::class, array(
                'constraints' => new \Symfony\Component\Validator\Constraints\Regex(
                        array(
                    'pattern' => "/^([A-Z]\w*\b\s*){2}$/",
                    'message' => 'You should input your firstname and lastname. For example, Taras Omelianchuk',
                        )
                )
                    )
            )
            ->add('cv', FileType::class, array(
                'label' => 'CV',
                'required' => true,
                'constraints' => new \Symfony\Component\Validator\Constraints\File(
                        array(
                    'maxSize' => "3M",
                        )
                )
                    )
            )
            ->add('date', DateType::class)
            ->add('save', SubmitType::class, array('label' => 'Send CV'))
            ->getForm();

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
      $file = $form->getData()->getCv();
      $name = str_replace(' ', '_', $form->getData()->getName());

      // Generate a unique name for the file before saving it
      $fileName = $name . '_' . md5(uniqid()) . '.' . $file->guessExtension();
      // Move the file to the directory where brochures are stored
      $file->move(
              $this->get('kernel')->getRootDir() . '/../var', $fileName
      );

      return $this->redirectToRoute('blog_front_article', array('date' => $form->getData()->getDate()->format('Y-m-d')));
    }

    return $this->render('default/form.html.twig', array('form' => $form->createView()));
  }

  public function congratulateAction(Request $request)
  {
    $response = '';

    if ($request->query->has('date')) {
      $response = '' . 
        '<html>' . 
          '<body style="text-align:center;">' . 
            '<h2>Спасибо за проявленный интерес к нашей компании. </h2>' . 
            '<h3>Мы ждем вас ' . $request->query->get('date') . ' в нашем офисe<h3>' .
          '</body>' .
        '</html>';
    }

    return new \Symfony\Component\HttpFoundation\Response($response);
  }

}
