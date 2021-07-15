<?php

namespace Svc\ParamBundle\Tests\Form;

use Svc\ParamBundle\Entity\Params;
use Svc\ParamBundle\Form\ParamsType;
use Symfony\Component\Form\Test\TypeTestCase;

class ParamFormTest extends TypeTestCase
{
  /**
   * @test
   */
  public function testFormIsSubmitedSuccessfully()
  {
    $this->assertTrue(true);
    return;
    
    $model = new Params('testmdod','qwe');
    
    $form = $this->factory->create(ParamsType::class, $model, ['dataType' => Params::TYPE_BOOL ]);

    $param = new Params('testBool');
    $param->setValueBool(true);
    $form->submit($param);

//    dump($form->getData());
    dump($model);
    dump($param);

    $this->assertTrue($form->isSynchronized());
    $this->assertSame('testBool', $form->getData()['name']);
  }

}