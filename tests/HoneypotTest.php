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
    public function test_get_raw_token()
    {
        $this->honeypot->speed(5)->setNameAttribute('honey_name');

        $this->assertEquals($this->honeypot->getRawToken(), ['time' => 10, 'speed' => 5]);
    }

    /** @test */
    public function test_get_honeypot_form_html()
    {
        $this->honeypot
            ->speed(5)
            ->setNameAttribute('honey_name')
            ->setTokenAttribute('honey_token');

        $this->encrypter->shouldReceive('encrypt')->with(function($token) { return '{"time":1000,"speed":5}'; })->once()->andReturn('55');
        // $this->encrypter->shouldReceive('encrypt')->once()->andReturn('ENCRYPTED_TOKEN');

        $actualHtml = $this->honeypot->html();
        $expectedHtml = '' .
            '<div id="honey_name_wrap" style="display:none;">' .
                '<input name="honey_name" type="text" value="" id="honey_name"/>' .
                '<input name="honey_token" type="text" value="55"/>' .
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
            $this->validateToken(['time' => 1]),
            'Validate should pass when values are before current time.'
        );
    }

    /** @test */
    public function it_fails_validation_when_values_are_after_current_time()
    {
        $this->assertFalse(
            $this->validateToken(['time' => 10]),
            'Validate should fail when values are after current time.'
        );
    }

    /** @test */
    public function it_fails_validation_when_token_is_invalid()
    {
        $this->assertFalse(
            $this->validateToken(['time' => 'bar']),
            'Validate should fail when decrypted time value is not numeric.'
        );

        $this->assertFalse(
            $this->validateToken(['time' => '']),
            'Validate should fail when decrypted time value is empty.'
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

        $this->honeypot->decryptToken('aa');
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
        $this->honeypot->decryptToken('aa');
    }

    private function validateToken(array $token)
    {
        $this->encrypter
            ->shouldReceive('decrypt')
            ->with('foo')->once()
            ->andReturn(json_encode($token));

        return $this->honeypot->speed(5)->validateToken('foo');
    }

}