Honeypot spam prevention
=========

## How does it work? 

"Honeypot" method of spam prevention is a simple and effective way to defer some of the spam bots that come to your site. This technique is based on creating an input field that should be left empty by the real users of the application but will most likely be filled out by spam bots. 

This package creates a hidden DIV with two fields in it, honeypot field (like "my_name") and a honeytime field - an encrypted timestamp that marks the moment when the page was served to the user. When the form containing these inputs invisible to the user is submitted to your application, a custom validator that comes with the package checks that the honeypot field is empty and also checks the time it took for the user to fill out the form. If the form was filled out too quickly (i.e. less than 5 seconds) or if there was a value put in the honeypot field, this submission is most likely from a spam bot.

## Installation:

In your terminal type : `composer require msurguy/honeypot` and provide "dev-master" as the version of the package. Or open up composer.json and add the following line under "require":

    {
        "require": {
            "msurguy/honeypot": "dev-master"
        }
    }

**If using with Laravel**

Install the composer dependency as above, then add this line to `providers` array in `app/config/app.php`:

    'Msurguy\Honeypot\HoneypotServiceProvider',

Supported Laravel versions: >= 4.2. For 4.2 and below, use version 0.3.2 of Honeypot.

## Usage in Laravel

### With forms & validation

Add the hidden DIV containing honeypot fields to your form by inserting `Form::honeypot` macro like this:

    {{ Form::open('contact') }}
        ...
        {{ Form::honeypot('my_name', 'my_time') }}
        ...
    {{ Form::close() }}

Then add the validation rules, matching the `my_name` and `my_time` fields:

    $rules = array(
        'email'     => "required|email",
        ...
        'my_name'   => 'honeypot',
        'my_time'   => 'required|honeytime:5'
    );

    $validator = Validator::make(Input::get(), $rules);

Please note that `honeytime` takes a parameter specifying number of seconds it should take for the user to fill out the form. If it takes less time than that the form is considered a spam submission.

## General usage (non-Laravel)

Initialise the honeypot

    $encrypter = new Illuminate\Encryption\Encrypter('my_super_secret_app_key');
    $honeypot = new Msurguy\Honeypot\Honeypot($encrypter);

In your view output the honeypot html

    <form method="post">
        ...
        <?= $honeypot->html('my_name', 'my_time'); ?>
        ...
    </form>

Then in your controller validate the honeypot:

    if (!$honeypot->isValid($_POST['my_name'], $_POST['my_time']))
    {
        // Honeypot failed, redirect with error?
    }

That's it! Enjoy getting less spam in your inbox. If you need stronger spam protection, consider using [Akismet](https://github.com/kenmoini/akismet) or [reCaptcha](https://github.com/dontspamagain/recaptcha)   

## Credits

Based on work originally created by Ian Landsman: <https://github.com/ianlandsman/Honeypot>

## License

This work is MIT-licensed by Maksim Surguy.