<?php namespace Msurguy\Tests;

use Mockery;
use Msurguy\Honeypot\Honeypot;
use Illuminate\Encryption\Encrypter;
use Illuminate\Encryption\DecryptException;

class HoneypotTest extends \PHPUnit_Framework_TestCase {

    private $honeypot;
    private $encrypter;

    public function setUp()
    {
        $this->encrypter = Mockery::mock('Illuminate\Encryption\Encrypter');
        $this->honeypot = new Honeypot($this->encrypter);
    }

    public function tearDown()
    {
        Mockery::close();
    }

    /** @test */
    public function test_get_honeypot_form_html()
    {
        $this->encrypter->shouldReceive('encrypt')->once()->andReturn('ENCRYPTED_TIME');

        $actualHtml = $this->honeypot->html('honey_name', 'honey_time');
        $expectedHtml = '' .
            '<div id="honey_name_wrap" style="display:none;">' . "\r\n" .
                '<input name="honey_name" type="text" value="" id="honey_name"/>' . "\r\n" .
                '<input name="honey_time" type="text" value="ENCRYPTED_TIME"/>' . "\r\n" .
            '</div>';

        $this->assertEquals($actualHtml, $expectedHtml);
    }

    /** @test */
    public function it_passes_validation_when_value_is_empty()
    {
        $this->assertTrue(
            $this->honeypot->validateHoneypot(''),
            'Validate should pass when value is empty.'
        );
    }

    /** @test */
    public function it_fails_validation_when_value_is_not_empty()
    {
        $this->assertFalse(
            $this->honeypot->validateHoneypot('foo'),
            'Validate should fail when value is not empty.'
        );
    }

    /** @test */
    public function it_passes_validation_when_values_are_before_current_time()
    {
        $this->assertTrue(
            $this->validateHoneyTime(1),
            'Validate should pass when values are before current time.'
        );
    }

    /** @test */
    public function it_fails_validation_when_values_are_after_current_time()
    {
        $this->assertFalse(
            $this->validateHoneyTime(10),
            'Validate should fail when values are after current time.'
        );
    }

    /** @test */
    public function it_fails_validation_when_value_is_not_numeric()
    {
        $this->assertFalse(
            $this->validateHoneyTime('bar'),
            'Validate should fail when decrypted value is not numeric.'
        );
    }

     /** @test */
    public function it_can_determine_correct_valid_state()
    {
        $this->encrypter->shouldReceive('decrypt')->with('foo')->andReturn(100);

        // $this->assertFalse($this->honeypot->isValid('ee'));
        // $this->assertTrue($this->honeypot->speed(900)->isValid('', 'foo'));
        // $this->assertFalse($this->honeypot->speed(99)->isValid('', 'foo'));
    }

    /** @test */
    public function it_catches_exception_when_cannot_decrypt()
    {
        $this->encrypter
            ->shouldReceive('decrypt')
            ->andThrow(Mockery::mock('Illuminate\Encryption\DecryptException'));

        $this->honeypot->decryptTime('aa');
    }

    /**
     * @test
     * @expectedException \Exception
     */
    public function it_throws_uncaught_exception_when_cannot_decrypt()
    {
        $this->encrypter
            ->shouldReceive('decrypt')
            ->andThrow(new \Exception('blah'));
        $this->honeypot->decryptTime('aa');
    }

    private function validateHoneyTime($time)
    {
        $this->encrypter
            ->shouldReceive('decrypt')
            ->with('foo')->once()
            ->andReturn($time);

        return $this->honeypot->speed(5)->validateHoneytime('foo');
    }

}