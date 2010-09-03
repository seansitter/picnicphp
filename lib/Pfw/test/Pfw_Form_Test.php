<?php

require('../../Pfw/UnitTest/PHPUnit/FwkTestCase.php');
Pfw_Loader::loadClass('Pfw_Form');

class Pfw_Form_Test extends Pfw_UnitTest_PHPUnit_FwkTestCase
{
	function testForm() 
	{
	   $form_data = array(
	     'test_field_1' => 'test_value_1',
	     'test_field_2' => 'test_value_2'
	   );
	   $form = new Pfw_Form($form_data);
	   $this->assertEquals('test_value_1', $form->test_field_1);
	   $this->assertEquals('test_value_2', $form->test_field_2);
	}
	
	function testFormSet() 
	{
		$form_data = array();
		$form_data[0]['test_field'] = 'test_value';
		$fs = Pfw_Form::getFormSet($form_data);
		$this->assertEquals('test_value', $fs[0]->test_field);
	}
	
	function testFormSetStrIdx() 
	{
		$form_data = array();
        $form_data['myindex']['foo'] = 'bar';
        $fs = Pfw_Form::getFormSet($form_data);
        $this->assertEquals('bar', $fs['myindex']->foo);
	}
}