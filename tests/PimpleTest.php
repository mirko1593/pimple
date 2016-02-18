<?php 

class PimpleTest extends PHPUnit_Framework_TestCase 
{
	protected $pimple;

	public function setUp()
	{
		$this->pimple = new Acme\Pimple;	
	}

	/** @test */
	public function setParameter()
	{
		$this->pimple['param'] = 'value';

		$this->assertEquals('value', $this->pimple['param']);		
	}

	/** @test */
	public function setService()
	{
		$this->pimple['service'] = function () {
			return new Service;
		};

		$this->assertInstanceOf('Service', $this->pimple['service']);
	}
}
